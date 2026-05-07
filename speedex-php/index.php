<?php
require_once __DIR__ . '/config/db.php';
$hubs = $pdo->query("SELECT * FROM hubs")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div>
    <h1>Fast. Safe. Reliable<br><span class="accent">Delivery All Over Bangladesh</span></h1>
    <p>Send your parcel easily and we will deliver with care.</p>
    <a href="#send" class="btn large">Send Parcel Now</a>
  </div>
</section>

<section class="features">
  <div class="card"><h3>Easy Booking</h3><p>Book your parcel in just a few steps.</p></div>
  <div class="card"><h3>Real-time Tracking</h3><p>Track your parcel in real-time.</p></div>
  <div class="card"><h3>Secure Delivery</h3><p>We ensure safe and secure delivery.</p></div>
  <div class="card"><h3>Wide Network</h3><p>Delivery across all major cities.</p></div>
</section>

<section id="send" class="send-parcel card">
  <h2>Send Parcel</h2>
  <form method="POST" action="/api/create_parcel.php">
    <div class="grid-2">
      <fieldset><legend>From (Sender)</legend>
        <input name="sender_name" placeholder="Name" required>
        <input name="sender_phone" placeholder="Phone" required>
        <input name="sender_address" placeholder="Address" required>
        <select name="sender_hub_id" required>
          <?php foreach ($hubs as $h): ?><option value="<?= $h['id'] ?>"><?= $h['name'] ?></option><?php endforeach; ?>
        </select>
      </fieldset>
      <fieldset><legend>To (Receiver)</legend>
        <input name="receiver_name" placeholder="Name" required>
        <input name="receiver_phone" placeholder="Phone" required>
        <input name="receiver_address" placeholder="Address" required>
        <select name="receiver_hub_id" required>
          <?php foreach ($hubs as $h): ?><option value="<?= $h['id'] ?>"><?= $h['name'] ?></option><?php endforeach; ?>
        </select>
      </fieldset>
    </div>
    <fieldset><legend>Parcel Information</legend>
      <select name="parcel_type"><option>Document</option><option>Package</option><option>Fragile</option></select>
      <input name="weight" type="number" step="0.1" placeholder="Weight (kg)" required>
      <input name="description" placeholder="Description">
    </fieldset>
    <fieldset><legend>Payment</legend>
      <label><input type="radio" name="payment_type" value="sender" checked> Sender Pay</label>
      <label><input type="radio" name="payment_type" value="receiver"> Receiver Pay</label>
    </fieldset>
    <button type="submit" class="btn large">Book Parcel</button>
  </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
