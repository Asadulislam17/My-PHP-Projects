<?php
/**
 * ══════════════════════════════════════════════
 * API v1 — INQUIRIES ENDPOINT
 * GET    /api/v1/inquiries
 * GET    /api/v1/inquiries/{id}
 * POST   /api/v1/inquiries
 * PUT    /api/v1/inquiries/{id}/reply
 * PUT    /api/v1/inquiries/{id}/close
 * DELETE /api/v1/inquiries/{id}
 * ══════════════════════════════════════════════
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$db     = Database::getInstance();

function ok(mixed $data, string $msg = 'success', int $code = 200): never {
    http_response_code($code);
    echo json_encode(['status'=>'success','message'=>$msg,'data'=>$data,'ts'=>date('c')], JSON_UNESCAPED_UNICODE);
    exit;
}
function err(string $msg, int $code = 400, array $errors = []): never {
    http_response_code($code);
    echo json_encode(['status'=>'error','message'=>$msg,'errors'=>$errors,'ts'=>date('c')], JSON_UNESCAPED_UNICODE);
    exit;
}
function getUser(): ?array {
    if (isset($_SESSION['user_id'])) return ['id'=>$_SESSION['user_id'],'role'=>$_SESSION['user_role']];
    $token = str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (!$token) return null;
    $db = Database::getInstance();
    return $db->queryOne(
        "SELECT u.id, r.name role FROM users u JOIN roles r ON r.id=u.role_id
         WHERE u.remember_token=? AND u.status='active'", [$token]
    ) ?: null;
}
function mustAuth(): array { $u = getUser(); if (!$u) err('Unauthorized',401); return $u; }

/* ── GET /api/v1/inquiries ── */
if ($method==='GET' && $path==='inquiries') {
    $user   = mustAuth();
    $page   = max(1,(int)($_GET['page']??1));
    $pp     = min(50,(int)($_GET['per_page']??20));
    $off    = ($page-1)*$pp;
    $where  = match($user['role']) {
        'admin' => '1=1',
        'agent' => 'i.agent_id='.$user['id'],
        default => 'i.sender_id='.$user['id'],
    };
    $status = $_GET['status'] ?? '';
    if ($status) $where .= " AND i.status='".addslashes($status)."'";

    $total = $db->queryOne("SELECT COUNT(*) c FROM inquiries i WHERE $where")['c'];
    $rows  = $db->query(
        "SELECT i.*,p.title prop_title,p.id prop_id,
                s.name sender_name,s.email sender_email,s.phone sender_phone,
                a.name agent_name,a.email agent_email
         FROM inquiries i
         JOIN properties p ON p.id=i.property_id
         JOIN users s ON s.id=i.sender_id
         JOIN users a ON a.id=i.agent_id
         WHERE $where ORDER BY i.created_at DESC LIMIT $pp OFFSET $off"
    );
    ok(['inquiries'=>$rows,'pagination'=>['total'=>$total,'page'=>$page,'per_page'=>$pp,'last_page'=>(int)ceil($total/$pp)]]);
}

/* ── GET /api/v1/inquiries/{id} ── */
if ($method==='GET' && preg_match('/^inquiries\/(\d+)$/',$path,$m)) {
    $user = mustAuth();
    $inq  = $db->queryOne(
        "SELECT i.*,p.title prop_title,s.name sender_name,a.name agent_name
         FROM inquiries i
         JOIN properties p ON p.id=i.property_id
         JOIN users s ON s.id=i.sender_id
         JOIN users a ON a.id=i.agent_id
         WHERE i.id=?", [(int)$m[1]]
    );
    if (!$inq) err('Inquiry not found',404);
    if ($user['role']!=='admin' && $inq['sender_id']!=$user['id'] && $inq['agent_id']!=$user['id']) err('Forbidden',403);
    ok($inq);
}

/* ── POST /api/v1/inquiries ── */
if ($method==='POST' && $path==='inquiries') {
    $user    = mustAuth();
    $propId  = (int)($body['property_id'] ?? 0);
    $message = trim($body['message'] ?? '');
    if (!$propId || strlen($message) < 10) err('property_id ও কমপক্ষে ১০ অক্ষরের message দিন',422);
    $prop = $db->queryOne("SELECT id,user_id,status FROM properties WHERE id=?",[$propId]);
    if (!$prop || $prop['status']!=='approved') err('Property পাওয়া যায়নি',404);
    if ($prop['user_id']==$user['id']) err('নিজের property তে inquiry করা যাবে না',400);
    $exists = $db->queryOne(
        "SELECT id FROM inquiries WHERE property_id=? AND sender_id=? AND status='pending'",
        [$propId,$user['id']]
    );
    if ($exists) err('এই property তে আপনার একটি open inquiry আছে',409);
    $db->execute(
        "INSERT INTO inquiries (property_id,sender_id,agent_id,message) VALUES (?,?,?,?)",
        [$propId,$user['id'],$prop['user_id'],$message]
    );
    ok(['inquiry_id'=>$db->lastInsertId()],'Inquiry পাঠানো হয়েছে',201);
}

/* ── PUT /api/v1/inquiries/{id}/reply ── */
if ($method==='PUT' && preg_match('/^inquiries\/(\d+)\/reply$/',$path,$m)) {
    $user  = mustAuth();
    $reply = trim($body['reply'] ?? '');
    if (!$reply) err('reply text required',422);
    $inq = $db->queryOne("SELECT * FROM inquiries WHERE id=?",[(int)$m[1]]);
    if (!$inq) err('Not found',404);
    if ($inq['agent_id']!=$user['id'] && $user['role']!=='admin') err('Forbidden',403);
    $db->execute(
        "UPDATE inquiries SET reply=?,status='replied',replied_at=NOW() WHERE id=?",
        [$reply,(int)$m[1]]
    );
    ok([],'Reply পাঠানো হয়েছে');
}

/* ── PUT /api/v1/inquiries/{id}/close ── */
if ($method==='PUT' && preg_match('/^inquiries\/(\d+)\/close$/',$path,$m)) {
    $user = mustAuth();
    $inq  = $db->queryOne("SELECT * FROM inquiries WHERE id=?",[(int)$m[1]]);
    if (!$inq) err('Not found',404);
    if ($inq['agent_id']!=$user['id'] && $inq['sender_id']!=$user['id'] && $user['role']!=='admin') err('Forbidden',403);
    $db->execute("UPDATE inquiries SET status='closed' WHERE id=?",[(int)$m[1]]);
    ok([],'Inquiry closed');
}

/* ── DELETE /api/v1/inquiries/{id} (admin only) ── */
if ($method==='DELETE' && preg_match('/^inquiries\/(\d+)$/',$path,$m)) {
    $user = mustAuth();
    if ($user['role']!=='admin') err('Forbidden',403);
    $db->execute("DELETE FROM inquiries WHERE id=?",[(int)$m[1]]);
    ok([],'Deleted');
}

err('Endpoint not found',404);