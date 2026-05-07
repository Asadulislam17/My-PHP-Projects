<?php require_once __DIR__ . '/config/db.php'; include __DIR__ . '/includes/header.php'; ?>
<section class="card">
  <h1>Track Your Parcel</h1>
  <div class="track-form">
    <input id="tid" placeholder="Enter Tracking ID (e.g. SPX12345ABCDE)">
    <button onclick="track()">Track</button>
  </div>
  <div id="result"></div>
</section>
<script>
async function track(){
  const tid = document.getElementById('tid').value.trim();
  if(!tid) return;
  const r = await fetch('/api/track_parcel.php?tracking_id='+encodeURIComponent(tid));
  const d = await r.json();
  const el = document.getElementById('result');
  if(d.error){ el.innerHTML = '<p class="error">'+d.error+'</p>'; return; }
  let html = '<h3>'+d.parcel.tracking_id+' — '+d.parcel.status+'</h3><ul class="timeline">';
  d.logs.forEach(l => { html += '<li>✔ '+l.status+' — '+(l.hub_name||'')+' <small>'+l.timestamp+'</small></li>'; });
  html += '</ul><p>Payment: '+d.parcel.payment_type+'</p>';
  el.innerHTML = html;
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
