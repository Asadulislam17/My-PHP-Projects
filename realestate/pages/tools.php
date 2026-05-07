<?php
require_once __DIR__ . '/../config/Database.php';
$db = Database::getInstance();
$materialRates = $db->query("SELECT * FROM material_rates ORDER BY id");
$activeTool = $_GET['tool'] ?? 'emi';
?>

<div class="tools-page">
  <div class="tools-header">
    <div class="container">
      <h1>স্মার্ট টুলস</h1>
      <p>আপনার রিয়েল এস্টেট সিদ্ধান্ত নিতে সাহায্য করুন</p>
      <div class="tools-tabs">
        <a href="?page=tools&tool=emi"
           class="tool-tab <?= $activeTool === 'emi' ? 'active' : '' ?>">
          <i class="bi bi-calculator me-2"></i>EMI Calculator
        </a>
        <a href="?page=tools&tool=estimator"
           class="tool-tab <?= $activeTool === 'estimator' ? 'active' : '' ?>">
          <i class="bi bi-building-gear me-2"></i>নির্মাণ খরচ
        </a>
        <a href="?page=compare"
           class="tool-tab">
          <i class="bi bi-layers me-2"></i>তুলনা করুন
        </a>
      </div>
    </div>
  </div>

  <div class="container py-5">

    <!-- ===== EMI CALCULATOR ===== -->
    <?php if ($activeTool === 'emi'): ?>
    <div class="row g-5 align-items-start">
      <div class="col-lg-5">
        <div class="tool-card">
          <h3 class="tool-title">
            <i class="bi bi-calculator text-accent me-2"></i>EMI Calculator
          </h3>
          <p class="tool-subtitle">মাসিক কিস্তি হিসাব করুন</p>

          <div class="mb-4">
            <label class="tool-label">
              ঋণের পরিমাণ (BDT)
              <span class="tool-value" id="loanDisplay">৳৫০,০০,০০০</span>
            </label>
            <input type="range" id="loanAmount" class="tool-range"
                   min="500000" max="50000000" step="100000" value="5000000">
            <div class="range-labels">
              <span>৫ লাখ</span><span>৫ কোটি</span>
            </div>
          </div>

          <div class="mb-4">
            <label class="tool-label">
              সুদের হার (%)
              <span class="tool-value" id="rateDisplay">9.5%</span>
            </label>
            <input type="range" id="interestRate" class="tool-range"
                   min="5" max="20" step="0.5" value="9.5">
            <div class="range-labels">
              <span>5%</span><span>20%</span>
            </div>
          </div>

          <div class="mb-4">
            <label class="tool-label">
              মেয়াদ (বছর)
              <span class="tool-value" id="tenureDisplay">20 বছর</span>
            </label>
            <input type="range" id="tenure" class="tool-range"
                   min="1" max="30" step="1" value="20">
            <div class="range-labels">
              <span>১ বছর</span><span>৩০ বছর</span>
            </div>
          </div>

          <button onclick="calculateEMI()" class="btn-accent w-100">
            <i class="bi bi-calculator me-2"></i>হিসাব করুন
          </button>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="tool-result-card" id="emiResult">
          <div class="emi-summary">
            <div class="emi-main">
              <small>মাসিক EMI</small>
              <h2 id="emiAmount">৳৪৬,৬০৮</h2>
            </div>
            <div class="emi-details">
              <div class="emi-detail-item">
                <span>মূল ঋণ</span>
                <strong id="principalDisplay">৳৫০,০০,০০০</strong>
              </div>
              <div class="emi-detail-item">
                <span>মোট সুদ</span>
                <strong id="totalInterestDisplay" class="text-accent">৳৬১,৮৫,৯২০</strong>
              </div>
              <div class="emi-detail-item">
                <span>মোট পরিশোধ</span>
                <strong id="totalPaymentDisplay">৳১,১১,৮৫,৯২০</strong>
              </div>
            </div>
          </div>

          <!-- Pie Chart -->
          <div class="chart-wrap">
            <canvas id="emiChart" width="300" height="300"></canvas>
            <div class="chart-legend">
              <div class="legend-item">
                <span class="legend-color" style="background:#0F172A"></span>
                মূল ঋণ
              </div>
              <div class="legend-item">
                <span class="legend-color" style="background:#C5A059"></span>
                সুদ
              </div>
            </div>
          </div>

          <!-- Amortization Table -->
          <div class="amortization-wrap">
            <h6 class="mb-3">বার্ষিক কিস্তির সারসংক্ষেপ</h6>
            <div class="amort-table-scroll">
              <table class="amort-table" id="amortTable"></table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== CONSTRUCTION COST ESTIMATOR ===== -->
    <?php elseif ($activeTool === 'estimator'): ?>
    <div class="row g-5 align-items-start">
      <div class="col-lg-5">
        <div class="tool-card">
          <h3 class="tool-title">
            <i class="bi bi-building-gear text-accent me-2"></i>নির্মাণ খরচ হিসাব
          </h3>
          <p class="tool-subtitle">আপনার বাড়ির নির্মাণ খরচ জানুন</p>

          <div class="mb-3">
            <label class="tool-label">মোট আয়তন (sqft)</label>
            <input type="number" id="sqft" class="tool-input"
                   placeholder="যেমন: ১২০০" value="1200" min="100" oninput="calculateCost()">
          </div>

          <div class="mb-3">
            <label class="tool-label">নির্মাণ মান</label>
            <div class="quality-btns">
              <button class="quality-btn active" data-quality="basic" onclick="setQuality('basic',this)">
                সাধারণ
              </button>
              <button class="quality-btn" data-quality="standard" onclick="setQuality('standard',this)">
                স্ট্যান্ডার্ড
              </button>
              <button class="quality-btn" data-quality="premium" onclick="setQuality('premium',this)">
                প্রিমিয়াম
              </button>
            </div>
          </div>

          <!-- Material Rates Table -->
          <div class="mb-3">
            <label class="tool-label">বর্তমান উপকরণের দাম</label>
            <div class="material-rates-table">
              <?php foreach ($materialRates as $rate): ?>
              <div class="rate-row">
                <span class="rate-name"><?= htmlspecialchars($rate['name']) ?></span>
                <span class="rate-unit"><?= htmlspecialchars($rate['unit']) ?></span>
                <span class="rate-price">৳<?= number_format($rate['rate']) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <small class="text-muted">* Admin কর্তৃক আপডেটকৃত হার</small>
          </div>

          <!-- Custom Quantities -->
          <div class="mb-3">
            <label class="tool-label">পরিমাণ নির্ধারণ করুন</label>
            <div class="qty-inputs">
              <div class="qty-input-row">
                <label>সিমেন্ট (ব্যাগ)</label>
                <input type="number" id="qCement" class="qty-input" value="0" oninput="calculateCost()">
              </div>
              <div class="qty-input-row">
                <label>রড (টন)</label>
                <input type="number" id="qRod" class="qty-input" value="0" oninput="calculateCost()">
              </div>
              <div class="qty-input-row">
                <label>ইট (হাজার পিস)</label>
                <input type="number" id="qBricks" class="qty-input" value="0" oninput="calculateCost()">
              </div>
              <div class="qty-input-row">
                <label>বালু (cft)</label>
                <input type="number" id="qSand" class="qty-input" value="0" oninput="calculateCost()">
              </div>
            </div>
          </div>

          <button onclick="calculateCost()" class="btn-accent w-100">
            <i class="bi bi-calculator me-2"></i>হিসাব করুন
          </button>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="tool-result-card">
          <h5 class="mb-4">আনুমানিক নির্মাণ খরচ</h5>

          <div class="cost-summary">
            <div class="cost-main">
              <small>মোট আনুমানিক খরচ</small>
              <h2 id="totalCost" class="text-accent">৳০</h2>
              <small id="costPerSqft"></small>
            </div>
          </div>

          <div class="cost-breakdown" id="costBreakdown">
            <div class="cost-bar-item" id="barCement">
              <div class="cost-bar-label">
                <span>সিমেন্ট</span>
                <span id="costCement">৳০</span>
              </div>
              <div class="cost-bar-track">
                <div class="cost-bar-fill gold" id="fillCement" style="width:0%"></div>
              </div>
            </div>
            <div class="cost-bar-item">
              <div class="cost-bar-label">
                <span>রড</span>
                <span id="costRod">৳০</span>
              </div>
              <div class="cost-bar-track">
                <div class="cost-bar-fill navy" id="fillRod" style="width:0%"></div>
              </div>
            </div>
            <div class="cost-bar-item">
              <div class="cost-bar-label">
                <span>ইট</span>
                <span id="costBricks">৳০</span>
              </div>
              <div class="cost-bar-track">
                <div class="cost-bar-fill green" id="fillBricks" style="width:0%"></div>
              </div>
            </div>
            <div class="cost-bar-item">
              <div class="cost-bar-label">
                <span>বালু</span>
                <span id="costSand">৳০</span>
              </div>
              <div class="cost-bar-track">
                <div class="cost-bar-fill blue" id="fillSand" style="width:0%"></div>
              </div>
            </div>
            <div class="cost-bar-item">
              <div class="cost-bar-label">
                <span>লেবার (আনুমানিক)</span>
                <span id="costLabor">৳০</span>
              </div>
              <div class="cost-bar-track">
                <div class="cost-bar-fill purple" id="fillLabor" style="width:0%"></div>
              </div>
            </div>
          </div>

          <div class="cost-note mt-3">
            <i class="bi bi-info-circle text-accent me-2"></i>
            এটি একটি আনুমানিক হিসাব। বাস্তব খরচ স্থান, মান ও বাজারদর অনুযায়ী পরিবর্তিত হতে পারে।
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ============================================
// MATERIAL RATES FROM PHP
// ============================================
const materialRates = <?= json_encode(array_column($materialRates, 'rate', 'name')) ?>;

// ============================================
// EMI CALCULATOR
// ============================================
let emiChart = null;

function formatBDT(num) {
  return '৳' + Math.round(num).toLocaleString('en-IN');
}

// Sliders
['loanAmount','interestRate','tenure'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('input', () => {
    if (id === 'loanAmount')   document.getElementById('loanDisplay').textContent   = formatBDT(el.value);
    if (id === 'interestRate') document.getElementById('rateDisplay').textContent   = el.value + '%';
    if (id === 'tenure')       document.getElementById('tenureDisplay').textContent  = el.value + ' বছর';
    calculateEMI();
  });
});

function calculateEMI() {
  const loan    = parseFloat(document.getElementById('loanAmount')?.value   || 5000000);
  const rate    = parseFloat(document.getElementById('interestRate')?.value  || 9.5);
  const tenure  = parseFloat(document.getElementById('tenure')?.value        || 20);

  const monthlyRate = (rate / 100) / 12;
  const months      = tenure * 12;

  const emi = loan * monthlyRate * Math.pow(1 + monthlyRate, months)
              / (Math.pow(1 + monthlyRate, months) - 1);

  const totalPayment  = emi * months;
  const totalInterest = totalPayment - loan;

  if (document.getElementById('emiAmount')) {
    document.getElementById('emiAmount').textContent          = formatBDT(emi);
    document.getElementById('principalDisplay').textContent   = formatBDT(loan);
    document.getElementById('totalInterestDisplay').textContent= formatBDT(totalInterest);
    document.getElementById('totalPaymentDisplay').textContent = formatBDT(totalPayment);

    drawEMIChart(loan, totalInterest);
    buildAmortTable(loan, monthlyRate, months, emi);
  }
}

function drawEMIChart(principal, interest) {
  const ctx = document.getElementById('emiChart');
  if (!ctx) return;

  if (emiChart) emiChart.destroy();

  emiChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['মূল ঋণ', 'সুদ'],
      datasets: [{
        data: [principal, interest],
        backgroundColor: ['#0F172A', '#C5A059'],
        borderWidth: 0,
        hoverOffset: 8,
      }]
    },
    options: {
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ' + formatBDT(ctx.parsed)
          }
        }
      }
    }
  });
}

function buildAmortTable(loan, r, months, emi) {
  let balance = loan;
  let html = `<tr>
    <th>বছর</th><th>মোট কিস্তি</th>
    <th>মূল</th><th>সুদ</th><th>বাকি</th>
  </tr>`;

  for (let y = 1; y <= months / 12; y++) {
    let yearPrincipal = 0, yearInterest = 0;
    for (let m = 0; m < 12 && balance > 0; m++) {
      const interest  = balance * r;
      const principal = Math.min(emi - interest, balance);
      yearInterest  += interest;
      yearPrincipal += principal;
      balance       -= principal;
    }
    html += `<tr>
      <td>${y}</td>
      <td>${formatBDT(emi * 12)}</td>
      <td>${formatBDT(yearPrincipal)}</td>
      <td>${formatBDT(yearInterest)}</td>
      <td>${formatBDT(Math.max(0, balance))}</td>
    </tr>`;
  }
  const t = document.getElementById('amortTable');
  if (t) t.innerHTML = html;
}


// ============================================
// CONSTRUCTION COST ESTIMATOR
// ============================================
let currentQuality = 'basic';
const qualityMultiplier = { basic: 1, standard: 1.4, premium: 2 };

function setQuality(q, btn) {
  currentQuality = q;
  document.querySelectorAll('.quality-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  calculateCost();
}

function calculateCost() {
  const sqft      = parseFloat(document.getElementById('sqft')?.value || 0);
  const multiplier= qualityMultiplier[currentQuality];

  const cementRate = parseFloat(materialRates['Cement']     || 520);
  const rodRate    = parseFloat(materialRates['Rod (10mm)']  || 85000);
  const brickRate  = parseFloat(materialRates['Bricks']      || 12);
  const sandRate   = parseFloat(materialRates['Sand']        || 45);
  const laborRate  = parseFloat(materialRates['Labor (Mason)'] || 800);

  const qCement = parseFloat(document.getElementById('qCement')?.value || 0);
  const qRod    = parseFloat(document.getElementById('qRod')?.value    || 0);
  const qBricks = parseFloat(document.getElementById('qBricks')?.value || 0) * 1000;
  const qSand   = parseFloat(document.getElementById('qSand')?.value   || 0);

  // Auto-estimate if sqft given & no manual input
  const autoCement = sqft > 0 && qCement === 0 ? (sqft * 0.4 * multiplier) : qCement;
  const autoRod    = sqft > 0 && qRod    === 0 ? (sqft * 0.005 * multiplier) : qRod;
  const autoBricks = sqft > 0 && qBricks === 0 ? (sqft * 8 * multiplier) : qBricks;
  const autoSand   = sqft > 0 && qSand   === 0 ? (sqft * 1.5 * multiplier) : qSand;

  const cCement = autoCement * cementRate;
  const cRod    = autoRod    * rodRate;
  const cBricks = autoBricks * brickRate;
  const cSand   = autoSand   * sandRate;
  const cLabor  = sqft > 0 ? (sqft * laborRate * 0.3 * multiplier) : 0;

  const total   = cCement + cRod + cBricks + cSand + cLabor;

  if (document.getElementById('totalCost')) {
    document.getElementById('totalCost').textContent = formatBDT(total);
    document.getElementById('costCement').textContent = formatBDT(cCement);
    document.getElementById('costRod').textContent    = formatBDT(cRod);
    document.getElementById('costBricks').textContent = formatBDT(cBricks);
    document.getElementById('costSand').textContent   = formatBDT(cSand);
    document.getElementById('costLabor').textContent  = formatBDT(cLabor);

    if (sqft > 0) {
      document.getElementById('costPerSqft').textContent =
        'প্রতি sqft: ' + formatBDT(total / sqft);
    }

    // Bar widths
    const bars = [
      ['fillCement', cCement],
      ['fillRod',    cRod],
      ['fillBricks', cBricks],
      ['fillSand',   cSand],
      ['fillLabor',  cLabor],
    ];
    bars.forEach(([id, val]) => {
      const el = document.getElementById(id);
      if (el && total > 0) el.style.width = ((val / total) * 100) + '%';
    });
  }
}

// Auto-calculate on load
window.addEventListener('load', () => {
  if (document.getElementById('emiChart'))    calculateEMI();
  if (document.getElementById('totalCost'))  calculateCost();
});
</script>