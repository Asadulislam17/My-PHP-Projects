<?php
/**
 * ══════════════════════════════════════════════
 * API v1 — ANALYTICS ENDPOINT
 * GET /api/v1/analytics/overview      (admin)
 * GET /api/v1/analytics/properties    (agent/admin)
 * GET /api/v1/analytics/agent/{id}    (admin)
 * GET /api/v1/analytics/funnel        (admin)
 * GET /api/v1/analytics/trending      (public)
 * POST /api/v1/analytics/track        (public)
 * ══════════════════════════════════════════════
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim($_GET['path'] ?? '', '/');
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = Database::getInstance();

function ok(mixed $d, string $m='success', int $c=200):never { http_response_code($c); echo json_encode(['status'=>'success','message'=>$m,'data'=>$d,'generated_at'=>date('c')],JSON_UNESCAPED_UNICODE); exit; }
function err(string $m, int $c=400):never { http_response_code($c); echo json_encode(['status'=>'error','message'=>$m],JSON_UNESCAPED_UNICODE); exit; }
function getUser():?array { if(isset($_SESSION['user_id'])) return ['id'=>$_SESSION['user_id'],'role'=>$_SESSION['user_role']]; $t=str_replace('Bearer ','', $_SERVER['HTTP_AUTHORIZATION']??''); if(!$t) return null; return Database::getInstance()->queryOne("SELECT u.id,r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.remember_token=? AND u.status='active'",[$t])?:null; }
function mustAuth():array { $u=getUser(); if(!$u) err('Unauthorized',401); return $u; }
function mustAdmin():array { $u=mustAuth(); if($u['role']!=='admin') err('Admin only',403); return $u; }

/* ── POST /api/v1/analytics/track  (track property view) ── */
if ($method==='POST' && $path==='analytics/track') {
    $propId = (int)($body['property_id'] ?? 0);
    $type   = $body['type'] ?? 'view';

    if (!$propId) err('property_id required');

    // Insert view
    $db->execute(
        "INSERT INTO property_views (property_id, user_id, ip_address, user_agent, viewed_at)
         VALUES (?, ?, ?, ?, NOW())",
        [
            $propId,
            isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]
    );

    // Increment counter
    $db->execute("UPDATE properties SET views_count = views_count + 1 WHERE id = ?", [$propId]);

    // Recently viewed (logged in user)
    if (isset($_SESSION['user_id'])) {
        $db->execute(
            "INSERT INTO recently_viewed (user_id, property_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE viewed_at = NOW()",
            [$_SESSION['user_id'], $propId]
        );
    }

    ok(['tracked' => true]);
}

/* ── GET /api/v1/analytics/trending  (public) ── */
if ($method==='GET' && $path==='analytics/trending') {
    $limit   = min(20, (int)($_GET['limit'] ?? 10));
    $days    = min(90, (int)($_GET['days']  ?? 7));

    $trending = $db->query(
        "SELECT p.id, p.title, p.price, p.price_type, p.size_sqft,
                p.views_count, p.is_featured, p.is_verified,
                pt.name type_name, a.name area_name, d.name district_name,
                COUNT(pv.id) AS recent_views,
                (SELECT image_path FROM property_images
                 WHERE property_id=p.id AND is_cover=1 LIMIT 1) AS cover_image
         FROM properties p
         JOIN property_types pt ON pt.id=p.type_id
         JOIN areas a           ON a.id=p.area_id
         JOIN districts d       ON d.id=a.district_id
         LEFT JOIN property_views pv ON pv.property_id=p.id
             AND pv.viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         WHERE p.status='approved'
         GROUP BY p.id
         ORDER BY recent_views DESC, p.views_count DESC
         LIMIT ?",
        [$days, $limit]
    );

    // Add cover URL
    $trending = array_map(fn($p) => array_merge($p, [
        'cover_url' => $p['cover_image']
            ? UPLOAD_URL . 'properties/' . $p['cover_image']
            : null,
    ]), $trending);

    ok(['trending' => $trending, 'period_days' => $days]);
}

/* ── GET /api/v1/analytics/overview  (admin only) ── */
if ($method==='GET' && $path==='analytics/overview') {
    mustAdmin();

    $range  = (int)($_GET['days'] ?? 30);
    $range  = in_array($range, [7,30,90,365]) ? $range : 30;
    $from   = date('Y-m-d', strtotime("-{$range} days"));

    // ── Core KPIs ──
    $kpis = [
        'total_users'       => $db->queryOne("SELECT COUNT(*) c FROM users")['c'],
        'new_users'         => $db->queryOne("SELECT COUNT(*) c FROM users WHERE created_at >= ?",[$from])['c'],
        'total_properties'  => $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='approved'")['c'],
        'new_properties'    => $db->queryOne("SELECT COUNT(*) c FROM properties WHERE created_at>=? AND status='approved'",[$from])['c'],
        'pending_approval'  => $db->queryOne("SELECT COUNT(*) c FROM properties WHERE status='pending'")['c'],
        'total_inquiries'   => $db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE created_at>=?",[$from])['c'],
        'total_bookings'    => $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE created_at>=?",[$from])['c'],
        'total_revenue'     => (float)$db->queryOne("SELECT COALESCE(SUM(amount),0) c FROM transactions WHERE status='success' AND created_at>=?",[$from])['c'],
        'total_views'       => $db->queryOne("SELECT COALESCE(SUM(views_count),0) c FROM properties WHERE status='approved'")['c'],
        'active_agents'     => $db->queryOne("SELECT COUNT(*) c FROM users WHERE role_id=2 AND status='active'")['c'],
        'active_subscriptions'=> $db->queryOne("SELECT COUNT(*) c FROM subscriptions WHERE status='active' AND expires_at >= CURDATE()")['c'],
    ];

    // ── Daily Users (chart) ──
    $dailyUsers = $db->query(
        "SELECT DATE(created_at) AS date, COUNT(*) AS count
         FROM users WHERE created_at >= ?
         GROUP BY DATE(created_at) ORDER BY date ASC",
        [$from]
    );

    // ── Daily Views (chart) ──
    $dailyViews = $db->query(
        "SELECT DATE(viewed_at) AS date, COUNT(*) AS count
         FROM property_views WHERE viewed_at >= ?
         GROUP BY DATE(viewed_at) ORDER BY date ASC",
        [$from]
    );

    // ── Daily Revenue (chart) ──
    $dailyRevenue = $db->query(
        "SELECT DATE(created_at) AS date, SUM(amount) AS amount
         FROM transactions WHERE status='success' AND created_at >= ?
         GROUP BY DATE(created_at) ORDER BY date ASC",
        [$from]
    );

    // ── Property Type Distribution ──
    $typeDistribution = $db->query(
        "SELECT pt.name, COUNT(p.id) AS count
         FROM property_types pt
         LEFT JOIN properties p ON p.type_id=pt.id AND p.status='approved'
         GROUP BY pt.id ORDER BY count DESC"
    );

    // ── Top Areas ──
    $topAreas = $db->query(
        "SELECT a.name, d.name district, COUNT(p.id) AS property_count,
                SUM(p.views_count) AS total_views
         FROM areas a
         JOIN districts d ON d.id=a.district_id
         LEFT JOIN properties p ON p.area_id=a.id AND p.status='approved'
         GROUP BY a.id HAVING property_count > 0
         ORDER BY property_count DESC LIMIT 10"
    );

    // ── Revenue by Gateway ──
    $revenueByGateway = $db->query(
        "SELECT COALESCE(gateway,'Unknown') AS gateway,
                COUNT(*) AS count, SUM(amount) AS total
         FROM transactions WHERE status='success'
         GROUP BY gateway ORDER BY total DESC"
    );

    // ── Inquiry Status ──
    $inquiryStatus = $db->query(
        "SELECT status, COUNT(*) AS count
         FROM inquiries GROUP BY status"
    );

    // ── Booking Status ──
    $bookingStatus = $db->query(
        "SELECT status, COUNT(*) AS count
         FROM bookings GROUP BY status"
    );

    // ── Top Agents by Performance ──
    $topAgents = $db->query(
        "SELECT u.id, u.name, u.email,
                COUNT(DISTINCT p.id) AS properties,
                COALESCE(SUM(p.views_count),0) AS total_views,
                COUNT(DISTINCT i.id) AS inquiries
         FROM users u
         JOIN roles r ON r.id=u.role_id AND r.name='agent'
         LEFT JOIN properties p ON p.user_id=u.id AND p.status='approved'
         LEFT JOIN inquiries  i ON i.agent_id=u.id
         GROUP BY u.id ORDER BY total_views DESC LIMIT 10"
    );

    ok([
        'kpis'               => $kpis,
        'period_days'        => $range,
        'period_from'        => $from,
        'charts' => [
            'daily_users'    => $dailyUsers,
            'daily_views'    => $dailyViews,
            'daily_revenue'  => $dailyRevenue,
        ],
        'distributions' => [
            'property_types' => $typeDistribution,
            'top_areas'      => $topAreas,
            'revenue_gateway'=> $revenueByGateway,
            'inquiry_status' => $inquiryStatus,
            'booking_status' => $bookingStatus,
        ],
        'top_agents'         => $topAgents,
    ]);
}

/* ── GET /api/v1/analytics/properties  (agent sees own, admin sees all) ── */
if ($method==='GET' && $path==='analytics/properties') {
    $user  = mustAuth();
    $range = (int)($_GET['days'] ?? 30);
    $from  = date('Y-m-d', strtotime("-{$range} days"));

    $where  = $user['role']==='admin' ? '1=1' : 'p.user_id='.$user['id'];

    // Per-property stats
    $props = $db->query(
        "SELECT p.id, p.title, p.price, p.price_type, p.status,
                p.views_count, p.is_featured, p.created_at,
                pt.name type_name, a.name area_name,
                COUNT(DISTINCT i.id)  AS inquiry_count,
                COUNT(DISTINCT b.id)  AS booking_count,
                COUNT(DISTINCT w.id)  AS wishlist_count,
                COUNT(DISTINCT pv.id) AS recent_views
         FROM properties p
         JOIN property_types pt ON pt.id=p.type_id
         JOIN areas a           ON a.id=p.area_id
         LEFT JOIN inquiries  i  ON i.property_id=p.id
         LEFT JOIN bookings   b  ON b.property_id=p.id
         LEFT JOIN wishlist   w  ON w.property_id=p.id
         LEFT JOIN property_views pv ON pv.property_id=p.id AND pv.viewed_at>=?
         WHERE $where
         GROUP BY p.id ORDER BY recent_views DESC",
        [$from]
    );

    // Aggregate stats
    $totals = [
        'total_views'    => array_sum(array_column($props,'views_count')),
        'total_inquiries'=> array_sum(array_column($props,'inquiry_count')),
        'total_bookings' => array_sum(array_column($props,'booking_count')),
        'total_wishlists'=> array_sum(array_column($props,'wishlist_count')),
    ];

    // Views over time (for chart)
    $viewsOverTime = $db->query(
        "SELECT DATE(pv.viewed_at) AS date, COUNT(*) AS count
         FROM property_views pv
         JOIN properties p ON p.id=pv.property_id
         WHERE pv.viewed_at>=? AND $where
         GROUP BY DATE(pv.viewed_at) ORDER BY date ASC",
        [$from]
    );

    ok([
        'properties'      => $props,
        'totals'          => $totals,
        'views_over_time' => $viewsOverTime,
        'period_days'     => $range,
    ]);
}

/* ── GET /api/v1/analytics/funnel  (admin) ── */
if ($method==='GET' && $path==='analytics/funnel') {
    mustAdmin();
    $range = (int)($_GET['days'] ?? 30);
    $from  = date('Y-m-d', strtotime("-{$range} days"));

    $views    = $db->queryOne("SELECT COUNT(*) c FROM property_views WHERE viewed_at>=?",[$from])['c'];
    $inquiries= $db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE created_at>=?",[$from])['c'];
    $bookings = $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE created_at>=?",[$from])['c'];
    $confirmed= $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE status='confirmed' AND created_at>=?",[$from])['c'];
    $completed= $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE status='completed' AND created_at>=?",[$from])['c'];

    ok([
        'period_days' => $range,
        'funnel' => [
            ['stage'=>'Property Views',    'count'=>$views,     'rate'=>'100%'],
            ['stage'=>'Inquiries',         'count'=>$inquiries, 'rate'=> $views>0 ? round($inquiries/$views*100,1).'%' : '0%'],
            ['stage'=>'Tour Booked',       'count'=>$bookings,  'rate'=> $views>0 ? round($bookings/$views*100,1).'%'  : '0%'],
            ['stage'=>'Tour Confirmed',    'count'=>$confirmed, 'rate'=> $bookings>0 ? round($confirmed/$bookings*100,1).'%' : '0%'],
            ['stage'=>'Tour Completed',    'count'=>$completed, 'rate'=> $bookings>0 ? round($completed/$bookings*100,1).'%' : '0%'],
        ],
    ]);
}

/* ── GET /api/v1/analytics/agent/{id}  (admin only) ── */
if ($method==='GET' && preg_match('/^analytics\/agent\/(\d+)$/',$path,$m)) {
    mustAdmin();
    $agentId = (int)$m[1];
    $range   = (int)($_GET['days'] ?? 30);
    $from    = date('Y-m-d', strtotime("-{$range} days"));

    $agent = $db->queryOne("SELECT id,name,email,created_at FROM users WHERE id=?",[$agentId]);
    if (!$agent) err('Agent not found',404);

    $stats = [
        'total_properties'  => $db->queryOne("SELECT COUNT(*) c FROM properties WHERE user_id=?",[$agentId])['c'],
        'approved_properties'=> $db->queryOne("SELECT COUNT(*) c FROM properties WHERE user_id=? AND status='approved'",[$agentId])['c'],
        'total_views'       => (int)$db->queryOne("SELECT COALESCE(SUM(views_count),0) c FROM properties WHERE user_id=?",[$agentId])['c'],
        'total_inquiries'   => $db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE agent_id=?",[$agentId])['c'],
        'pending_inquiries' => $db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE agent_id=? AND status='pending'",[$agentId])['c'],
        'total_bookings'    => $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE agent_id=?",[$agentId])['c'],
        'completed_bookings'=> $db->queryOne("SELECT COUNT(*) c FROM bookings WHERE agent_id=? AND status='completed'",[$agentId])['c'],
    ];

    $recentProperties = $db->query(
        "SELECT p.id,p.title,p.price,p.status,p.views_count,p.created_at,
                pt.name type_name, a.name area_name
         FROM properties p
         JOIN property_types pt ON pt.id=p.type_id
         JOIN areas a ON a.id=p.area_id
         WHERE p.user_id=? ORDER BY p.created_at DESC LIMIT 10",
        [$agentId]
    );

    ok(['agent'=>$agent,'stats'=>$stats,'recent_properties'=>$recentProperties,'period_days'=>$range]);
}

err('Endpoint not found',404);