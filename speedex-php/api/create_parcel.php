<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$data = json_input() ?: $_POST;
$required = ['sender_name','sender_phone','sender_address','sender_hub_id',
             'receiver_name','receiver_phone','receiver_address','receiver_hub_id',
             'parcel_type','weight','payment_type'];
foreach ($required as $f) {
  if (empty($data[$f])) { http_response_code(400); echo json_encode(['error'=>"Missing $f"]); exit; }
}

$tracking = generate_tracking_id();
$stmt = $pdo->prepare("INSERT INTO parcels
  (tracking_id,sender_name,sender_phone,sender_address,sender_hub_id,
   receiver_name,receiver_phone,receiver_address,receiver_hub_id,
   parcel_type,weight,description,payment_type,status)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'received')");
$stmt->execute([
  $tracking, $data['sender_name'], $data['sender_phone'], $data['sender_address'], $data['sender_hub_id'],
  $data['receiver_name'], $data['receiver_phone'], $data['receiver_address'], $data['receiver_hub_id'],
  $data['parcel_type'], $data['weight'], $data['description'] ?? '', $data['payment_type']
]);
$pid = $pdo->lastInsertId();

$pdo->prepare("INSERT INTO parcel_logs (parcel_id,status,hub_id,note) VALUES (?, 'received', ?, 'Parcel received at origin hub')")
    ->execute([$pid, $data['sender_hub_id']]);

echo json_encode(['success'=>true,'tracking_id'=>$tracking,'parcel_id'=>$pid]);
