<?php
/**
 * ══════════════════════════════════════════════
 * API v1 — BOOKINGS ENDPOINT
 * GET    /api/v1/bookings
 * GET    /api/v1/bookings/{id}
 * POST   /api/v1/bookings
 * PUT    /api/v1/bookings/{id}/confirm
 * PUT    /api/v1/bookings/{id}/cancel
 * PUT    /api/v1/bookings/{id}/complete
 * GET    /api/v1/bookings/available-slots
 * ══════════════════════════════════════════════
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$db     = Database::getInstance();

function ok(mixed $d, string $m='success', int $c=200):never { http_response_code($c); echo json_encode(['status'=>'success','message'=>$m,'data'=>$d,'ts'=>date('c')],JSON_UNESCAPED_UNICODE); exit; }
function err(string $m, int $c=400, array $e=[]):never { http_response_code($c); echo json_encode(['status'=>'error','message'=>$m,'errors'=>$e,'ts'=>date('c')],JSON_UNESCAPED_UNICODE); exit; }
function getUser():?array { if(isset($_SESSION['user_id'])) return ['id'=>$_SESSION['user_id'],'role'=>$_SESSION['user_role']]; $t=str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']??''); if(!$t) return null; return Database::getInstance()->queryOne("SELECT u.id,r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.remember_token=? AND u.status='active'",[$t])?:null; }
function mustAuth():array { $u=getUser(); if(!$u) err('Unauthorized',401); return $u; }

/* ── GET /api/v1/bookings/available-slots ── */
if ($method==='GET' && $path==='bookings/available-slots') {
    $propId = (int)($_GET['property_id'] ?? 0);
    $date   = $_GET['date'] ?? date('Y-m-d', strtotime('+1 day'));

    if (!$propId) err('property_id required',422);

    // Booked slots
    $bookedSlots = $db->query(
        "SELECT tour_time FROM bookings
         WHERE property_id=? AND tour_date=? AND status IN ('pending','confirmed')",
        [$propId, $date]
    );
    $booked = array_column($bookedSlots, 'tour_time');

    $allSlots = ['09:00','10:00','11:00','12:00','14:00','15:00','16:00','17:00'];
    $available = array_map(fn($s) => [
        'time'      => $s,
        'available' => !in_array($s, $booked),
        'label'     => date('g:i A', strtotime($s)),
    ], $allSlots);

    ok(['date'=>$date,'slots'=>$available]);
}

/* ── GET /api/v1/bookings ── */
if ($method==='GET' && $path==='bookings') {
    $user = mustAuth();
    $page = max(1,(int)($_GET['page']??1));
    $pp   = min(50,(int)($_GET['per_page']??20));
    $off  = ($page-1)*$pp;

    $where = match($user['role']) {
        'admin' => '1=1',
        'agent' => 'b.agent_id='.$user['id'],
        default => 'b.user_id='.$user['id'],
    };
    $status = $_GET['status'] ?? '';
    if ($status) $where .= " AND b.status='".addslashes($status)."'";

    $total = $db->queryOne("SELECT COUNT(*) c FROM bookings b WHERE $where")['c'];
    $rows  = $db->query(
        "SELECT b.*,
                p.title prop_title, p.address prop_address,
                u.name buyer_name, u.email buyer_email, u.phone buyer_phone,
                a.name agent_name, a.phone agent_phone
         FROM bookings b
         JOIN properties p ON p.id=b.property_id
         JOIN users u      ON u.id=b.user_id
         JOIN users a      ON a.id=b.agent_id
         WHERE $where
         ORDER BY b.tour_date DESC, b.tour_time DESC
         LIMIT $pp OFFSET $off"
    );
    ok(['bookings'=>$rows,'pagination'=>['total'=>$total,'page'=>$page,'per_page'=>$pp,'last_page'=>(int)ceil($total/$pp)]]);
}

/* ── GET /api/v1/bookings/{id} ── */
if ($method==='GET' && preg_match('/^bookings\/(\d+)$/',$path,$m)) {
    $user = mustAuth();
    $b    = $db->queryOne(
        "SELECT b.*,p.title prop_title,u.name buyer_name,a.name agent_name
         FROM bookings b
         JOIN properties p ON p.id=b.property_id
         JOIN users u ON u.id=b.user_id
         JOIN users a ON a.id=b.agent_id
         WHERE b.id=?", [(int)$m[1]]
    );
    if (!$b) err('Booking not found',404);
    if ($user['role']!=='admin' && $b['user_id']!=$user['id'] && $b['agent_id']!=$user['id']) err('Forbidden',403);
    ok($b);
}

/* ── POST /api/v1/bookings ── */
if ($method==='POST' && $path==='bookings') {
    $user    = mustAuth();
    $propId  = (int)($body['property_id'] ?? 0);
    $date    = $body['tour_date'] ?? '';
    $time    = $body['tour_time'] ?? '';
    $message = trim($body['message'] ?? '');

    if (!$propId || !$date || !$time) err('property_id, tour_date, tour_time required',422);
    if (strtotime($date) <= strtotime('today')) err('ভবিষ্যতের তারিখ দিন',422);

    $prop = $db->queryOne("SELECT id,user_id,status FROM properties WHERE id=?",[$propId]);
    if (!$prop || $prop['status']!=='approved') err('Property পাওয়া যায়নি',404);

    // Slot availability check
    $slotTaken = $db->queryOne(
        "SELECT id FROM bookings WHERE property_id=? AND tour_date=? AND tour_time=? AND status IN ('pending','confirmed')",
        [$propId, $date, $time]
    );
    if ($slotTaken) err('এই সময়টি আগেই বুক হয়ে গেছে। অন্য সময় বেছে নিন',409);

    // Duplicate booking check
    $dupCheck = $db->queryOne(
        "SELECT id FROM bookings WHERE property_id=? AND user_id=? AND status IN ('pending','confirmed')",
        [$propId, $user['id']]
    );
    if ($dupCheck) err('এই property তে আপনার একটি active booking আছে',409);

    $db->execute(
        "INSERT INTO bookings (property_id,user_id,agent_id,tour_date,tour_time,message)
         VALUES (?,?,?,?,?,?)",
        [$propId,$user['id'],$prop['user_id'],$date,$time,$message]
    );
    $bookingId = $db->lastInsertId();

    // Notify agent (log)
    $db->execute(
        "INSERT INTO activity_logs (user_id,action,details,ip_address) VALUES (?,?,?,?)",
        [$user['id'],'booking.created','Property #'.$propId.' Date:'.$date.' Time:'.$time,$_SERVER['REMOTE_ADDR']??'']
    );

    ok(['booking_id'=>$bookingId,'tour_date'=>$date,'tour_time'=>$time],'Tour বুক হয়েছে!',201);
}

/* ── PUT /api/v1/bookings/{id}/confirm ── */
if ($method==='PUT' && preg_match('/^bookings\/(\d+)\/confirm$/',$path,$m)) {
    $user = mustAuth();
    $b    = $db->queryOne("SELECT * FROM bookings WHERE id=?",[(int)$m[1]]);
    if (!$b) err('Not found',404);
    if ($b['agent_id']!=$user['id'] && $user['role']!=='admin') err('Forbidden',403);
    $db->execute("UPDATE bookings SET status='confirmed' WHERE id=?",[(int)$m[1]]);
    ok([],'Booking confirmed');
}

/* ── PUT /api/v1/bookings/{id}/cancel ── */
if ($method==='PUT' && preg_match('/^bookings\/(\d+)\/cancel$/',$path,$m)) {
    $user = mustAuth();
    $b    = $db->queryOne("SELECT * FROM bookings WHERE id=?",[(int)$m[1]]);
    if (!$b) err('Not found',404);
    if ($b['user_id']!=$user['id'] && $b['agent_id']!=$user['id'] && $user['role']!=='admin') err('Forbidden',403);
    $reason = trim($body['reason'] ?? '');
    $db->execute(
        "UPDATE bookings SET status='cancelled' WHERE id=?",
        [(int)$m[1]]
    );
    if ($reason) {
        $db->execute(
            "INSERT INTO activity_logs (user_id,action,details,ip_address) VALUES (?,?,?,?)",
            [$user['id'],'booking.cancelled','Reason: '.$reason,$_SERVER['REMOTE_ADDR']??'']
        );
    }
    ok([],'Booking cancelled');
}

/* ── PUT /api/v1/bookings/{id}/complete ── */
if ($method==='PUT' && preg_match('/^bookings\/(\d+)\/complete$/',$path,$m)) {
    $user = mustAuth();
    $b    = $db->queryOne("SELECT * FROM bookings WHERE id=?",[(int)$m[1]]);
    if (!$b) err('Not found',404);
    if ($b['agent_id']!=$user['id'] && $user['role']!=='admin') err('Forbidden',403);
    $db->execute("UPDATE bookings SET status='completed' WHERE id=?",[(int)$m[1]]);
    ok([],'Booking completed');
}

err('Endpoint not found',404);