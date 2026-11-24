<?php
$maxHrRev = max(1, ...array_map(fn($h) => (float)$h['revenue'], $hourlySales));
?>

<div class="page-header">
  <div>
    <div class="eyebrow">COUNTER WORKSPACE &middot; <?= strtoupper(htmlspecialchars(explode(' ', $currentUser['full_name'])[0])) ?></div>
    <h1 class="page-title">My dashboard</h1>
  </div>

  <div class="segmented">
    <a href="/staff?period=daily" class="<?= $period === 'daily' ? 'active' : '' ?>">Daily</a>
    <a href="/staff?period=weekly" class="<?= $period === 'weekly' ? 'active' : '' ?>">Weekly</a>
    <a href="/staff?period=monthly" class="<?= $period === 'monthly' ? 'active' : '' ?>">Monthly</a>
  </div>
</div>

<!-- 4 KPI Cards -->
<div class="kpi-grid-4">
  <div class="card kpi-card featured">
    <div class="eyebrow">My revenue</div>
    <div class="kpi-val">$<?= number_format($kpis['revenue'], 2) ?></div>
    <div style="font-size:12.5px;opacity:0.9;">
      <?= $kpis['revenue_delta'] >= 0 ? '&#8599;' : '&#8600;' ?> <?= abs($kpis['revenue_delta']) ?>% vs previous
    </div>
  </div>
  <div class="card kpi-card">
    <div class="eyebrow">My bills</div>
    <div class="kpi-val"><?= number_format($kpis['bills']) ?></div>
    <div style="font-size:12.5px;color:var(--ink-muted);">Issued in period</div>
  </div>
  <div class="card kpi-card">
    <div class="eyebrow">Average bill</div>
    <div class="kpi-val">$<?= number_format($kpis['avg_bill'], 2) ?></div>
    <div style="font-size:12.5px;color:var(--ink-muted);">Per transaction</div>
  </div>
  <div class="card kpi-card">
    <div class="eyebrow">Units dispensed</div>
    <div class="kpi-val"><?= number_format($kpis['units']) ?></div>
    <div style="font-size:12.5px;color:var(--ink-muted);">Total items</div>
  </div>
</div>

<div class="grid-2-1">
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <div>
        <div class="eyebrow">HOURLY PACE</div>
        <h3 style="margin:2px 0 0;font-size:16px;">My sales by hour</h3>
      </div>
      <span class="badge badge-green">#<?= $teamRank['rank'] ?> of <?= $teamRank['total'] ?> on the team</span>
    </div>
    <div class="bar-chart">
      <?php foreach ($hourlySales as $hr):
        $pct = round(((float)$hr['revenue'] / $maxHrRev) * 100);
      ?>
        <div class="bar-col">
          <div class="bar-fill <?= $pct >= 75 ? 'peak' : '' ?>" style="height: <?= max(4, $pct) ?>%;"></div>
          <div class="bar-lbl" style="font-size:10.5px;"><?= htmlspecialchars($hr['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px;">
    <div class="card kpi-card featured">
      <div class="eyebrow">COUNTER POS</div>
      <h3 class="serif" style="font-size:22px;margin:8px 0;">Customer at the counter?</h3>
      <p style="margin:0 0 16px;opacity:0.88;font-size:13px;">Search medicines, verify prescriptions, and print an 80mm thermal receipt.</p>
      <a href="/staff/checkout" class="btn btn-outline" style="color:var(--green-800);font-weight:700;">Start a new bill &rarr;</a>
    </div>

    <div class="card">
      <div class="eyebrow">AT A GLANCE</div>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">
        <div style="display:flex;justify-content:space-between;"><span>Today</span><strong class="mono">$<?= number_format($glance['today']['revenue'], 2) ?></strong></div>
        <div style="display:flex;justify-content:space-between;"><span>This week</span><strong class="mono">$<?= number_format($glance['week']['revenue'], 2) ?></strong></div>
        <div style="display:flex;justify-content:space-between;"><span>This month</span><strong class="mono">$<?= number_format($glance['month']['revenue'], 2) ?></strong></div>
      </div>
    </div>
  </div>
</div>

<div class="grid-2-1">
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <h3 style="margin:0;font-size:16px;">My recent bills</h3>
      <a href="/staff/sales" style="font-size:12.5px;color:var(--green-800);font-weight:600;">View all &rarr;</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Invoice</th><th>Customer</th><th>Payment</th><th style="text-align:right;">Total</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recentBills as $b): ?>
            <tr class="clickable" onclick="showReceipt(<?= $b['id'] ?>)">
              <td class="mono" style="font-weight:600;"><?= htmlspecialchars($b['invoice_no']) ?></td>
              <td><?= htmlspecialchars($b['customer_name'] ?: 'Walk-in') ?></td>
              <td><span class="badge badge-neutral"><?= htmlspecialchars($b['payment_method']) ?></span></td>
              <td class="mono" style="text-align:right;font-weight:600;">$<?= number_format((float)$b['total_amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3 style="margin:0 0 12px;font-size:16px;">My top sellers</h3>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($topSellers as $ts): ?>
        <div style="display:flex;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid var(--border-warm);">
          <div>
            <strong><?= htmlspecialchars($ts['medicine_name']) ?></strong>
            <div style="font-size:12px;color:var(--ink-muted);"><?= $ts['total_qty'] ?> units</div>
          </div>
          <span class="mono" style="font-weight:600;">$<?= number_format((float)$ts['total_revenue'], 2) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
