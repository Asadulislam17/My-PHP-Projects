<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$tid = $_GET['tracking_id'] ?? '';
if (!$tid) { http_response_code(400); echo json_encode(['error'=>'tracking_id required']); exit; }

$stmt = $pdo->prepare("SELECT p.*, sh.name AS sender_hub, rh.name AS receiver_hub
  FROM parcels p
  JOIN hubs sh ON sh.id=p.sender_hub_id
  JOIN hubs rh ON rh.id=p.receiver_hub_id
  WHERE tracking_id=?");
$stmt->execute([$tid]);
$parcel = $stmt->fetch();
if (!$parcel) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

$logs = $pdo->prepare("SELECT l.*, h.name AS hub_name FROM parcel_logs l
  LEFT JOIN hubs h ON h.id=l.hub_id WHERE parcel_id=? ORDER BY timestamp ASC");
$logs->execute([$parcel['id']]);

echo json_encode(['parcel'=>$parcel, 'logs'=>$logs->fetchAll()]);
