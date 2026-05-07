<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$data = json_input() ?: $_POST;
$pid = $data['parcel_id'] ?? null;
$status = $data['status'] ?? null;
$hub_id = $data['hub_id'] ?? null;

$valid = ['received','transit','arrived','ready','delivered'];
if (!$pid || !in_array($status, $valid)) {
  http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit;
}

$pdo->prepare("UPDATE parcels SET status=? WHERE id=?")->execute([$status, $pid]);
$pdo->prepare("INSERT INTO parcel_logs (parcel_id,status,hub_id) VALUES (?,?,?)")->execute([$pid, $status, $hub_id]);

// Bangla SMS messages (would integrate with SMS gateway here)
$messages = [
  'transit'   => 'আপনার পার্সেল ঢাকা হাব থেকে রওনা হয়েছে',
  'arrived'   => 'আপনার পার্সেল ময়মনসিংহ হাবে পৌঁছেছে',
  'ready'     => 'আপনার পার্সেল সংগ্রহের জন্য প্রস্তুত',
  'delivered' => 'আপনার পার্সেল সফলভাবে ডেলিভার হয়েছে',
];

echo json_encode(['success'=>true, 'sms'=>$messages[$status] ?? null]);
