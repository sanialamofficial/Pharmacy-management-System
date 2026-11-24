<?php
$periodLabel = match ($period) {
    'today' => 'Today',
    'month' => 'Last 30 days',
    default => 'Last 7 days',
};
$maxDayRev = max(1, ...array_map(fn($d) => (float)$d['revenue'], $revenueByDay));
$peakRev = max(array_map(fn($d) => (float)$d['revenue'], $revenueByDay));
?>

<div class="page-header">
  <div>
    <div class="eyebrow">GOOD <?= date('H') < 12 ? 'MORNING' : (date('H') < 17 ? 'AFTERNOON' : 'EVENING') ?>, <?= strtoupper(htmlspecialchars(explode(' ', $currentUser['full_name'])[0])) ?></div>
    <h1 class="page-title">Pharmacy overview</h1>
  </div>

  <div class="segmented">
    <a href="/admin?period=today" class="<?= $period === 'today' ? 'active' : '' ?>">Today</a>
    <a href="/admin?period=week" class="<?= $period === 'week' ? 'active' : '' ?>">Week</a>
    <a href="/admin?period=month" class="<?= $period === 'month' ? 'active' : '' ?>">Month</a>
  </div>
</div>

<!-- 4 KPI Cards -->
<div class="kpi-grid-4">
  <div class="card kpi-card featured">
    <div class="eyebrow">Revenue &middot; <?= $periodLabel ?></div>
    <div class="kpi-val">$<?= number_format($kpis['revenue'], 2) ?></div>
    <div style="font-size:12.5px;opacity:0.9;">
      <?= $kpis['revenue_delta'] >= 0 ? '&#8599;' : '&#8600;' ?> <?= abs($kpis['revenue_delta']) ?>% vs previous
    </div>
  </div>

  <div class="card kpi-card">
    <div class="eyebrow">Bills issued</div>
    <div class="kpi-val"><?= number_format($kpis['bills']) ?></div>
    <div style="font-size:12.5px;" class="<?= $kpis['bills_delta'] >= 0 ? 'badge badge-green' : 'badge badge-terracotta' ?>">
      <?= $kpis['bills_delta'] >= 0 ? '&#8599;' : '&#8600;' ?> <?= abs($kpis['bills_delta']) ?>% vs previous
    </div>
  </div>

  <div class="card kpi-card">
    <div class="eyebrow">Average bill</div>
    <div class="kpi-val">$<?= number_format($kpis['avg_bill'], 2) ?></div>
    <div style="font-size:12.5px;" class="<?= $kpis['avg_bill_delta'] >= 0 ? 'badge badge-green' : 'badge badge-terracotta' ?>">
      <?= $kpis['avg_bill_delta'] >= 0 ? '&#8599;' : '&#8600;' ?> <?= abs($kpis['avg_bill_delta']) ?>% vs previous
    </div>
  </div>

  <div class="card kpi-card">
    <div class="eyebrow">Units dispensed</div>
    <div class="kpi-val"><?= number_format($kpis['units']) ?></div>
    <div style="font-size:12.5px;" class="<?= $kpis['units_delta'] >= 0 ? 'badge badge-green' : 'badge badge-terracotta' ?>">
      <?= $kpis['units_delta'] >= 0 ? '&#8599;' : '&#8600;' ?> <?= abs($kpis['units_delta']) ?>% vs previous
    </div>
  </div>
</div>

<!-- Middle Row: Revenue by Day + Needs Restocking -->
<div class="grid-2-1">
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <div>
        <div class="eyebrow">DAILY TREND</div>
        <h3 style="margin:2px 0 0;font-size:16px;">Revenue by day</h3>
      </div>
      <span class="badge badge-neutral">Last 7 days</span>
    </div>
    <div class="bar-chart">
      <?php foreach ($revenueByDay as $day):
        $pct = round(((float)$day['revenue'] / $maxDayRev) * 100);
        $isPeak = (float)$day['revenue'] === $peakRev && $peakRev > 0;
      ?>
        <div class="bar-col">
          <div class="bar-val">$<?= number_format((float)$day['revenue'], 0) ?></div>
          <div class="bar-fill <?= $isPeak ? 'peak' : '' ?>" style="height: <?= max(6, $pct) ?>%;"></div>
          <div class="bar-lbl"><?= htmlspecialchars($day['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <div>
        <div class="eyebrow">BELOW <?= $config['low_stock_threshold'] ?> UNITS</div>
        <h3 style="margin:2px 0 0;font-size:16px;">Needs restocking</h3>
      </div>
      <span class="badge badge-terracotta"><?= count($lowStock) ?> items</span>
    </div>

    <?php if (empty($lowStock)): ?>
      <p style="color:var(--ink-muted);">All medicines are above the restock threshold.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($lowStock as $item): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-surface);border:1px solid var(--border-warm);border-radius:8px;">
            <div>
              <strong style="font-size:13.5px;"><?= htmlspecialchars($item['brand_name'] . ' ' . $item['strength']) ?></strong>
              <div style="font-size:12px;color:var(--terracotta);">
                <?= (int)$item['stock_qty'] === 0 ? 'Out of stock' : $item['stock_qty'] . ' units left' ?> &middot; <?= htmlspecialchars($item['shelf_location']) ?>
              </div>
            </div>
            <button type="button"
              class="btn btn-outline btn-sm open-restock-btn"
              data-id="<?= $item['id'] ?>"
              data-name="<?= htmlspecialchars($item['brand_name'] . ' ' . $item['strength']) ?>"
              data-stock="<?= $item['stock_qty'] ?>">Restock</button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Sales by Staff -->
<div class="card" style="margin-bottom:20px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <div>
      <div class="eyebrow">TEAM PERFORMANCE</div>
      <h3 style="margin:2px 0 0;font-size:16px;">Sales by staff</h3>
    </div>
    <a href="/admin/staff" class="btn btn-ghost btn-sm">Manage staff &rarr;</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Staff Member</th>
          <th>Share of Revenue</th>
          <th>Bills</th>
          <th>Avg Bill</th>
          <th style="text-align:right;">Revenue</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($salesByStaff as $row): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars($row['full_name']) ?></strong>
              <?php if ($row['status'] === 'suspended'): ?>
                <span class="badge badge-terracotta" style="margin-left:6px;">Suspended</span>
              <?php endif; ?>
            </td>
            <td style="min-width:200px;">
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="flex:1;height:7px;background:var(--bg-subtle);border-radius:999px;overflow:hidden;">
                  <div style="width:<?= $row['share_pct'] ?>%;height:100%;background:var(--green-800);"></div>
                </div>
                <span class="mono" style="font-size:12px;min-width:42px;"><?= number_format($row['share_pct'], 1) ?>%</span>
              </div>
            </td>
            <td class="mono"><?= $row['bills'] ?></td>
            <td class="mono">$<?= number_format($row['avg_bill'], 2) ?></td>
            <td class="mono" style="text-align:right;font-weight:600;">$<?= number_format($row['revenue'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Bottom 3 Cards: Top Medicines, Expiring Soon, Latest Bills -->
<div class="grid-3">
  <div class="card">
    <div class="eyebrow">BEST SELLERS</div>
    <h3 style="margin:2px 0 14px;font-size:16px;">Top medicines</h3>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($topMedicines as $tm): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border-warm);">
          <div>
            <div style="font-weight:600;"><?= htmlspecialchars($tm['medicine_name']) ?></div>
            <div style="font-size:12px;color:var(--ink-muted);"><?= $tm['total_qty'] ?> units</div>
          </div>
          <span class="mono" style="font-weight:600;">$<?= number_format((float)$tm['total_revenue'], 2) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="eyebrow">BATCH WATCH</div>
    <h3 style="margin:2px 0 14px;font-size:16px;">Expiring soon</h3>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php if (empty($expiringSoon)): ?>
        <div style="color:var(--ink-muted);">No batches expiring within 30 days.</div>
      <?php else: ?>
        <?php foreach ($expiringSoon as $exp): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border-warm);">
            <div>
              <div style="font-weight:600;"><?= htmlspecialchars($exp['brand_name'] . ' ' . $exp['strength']) ?></div>
              <div style="font-size:12px;color:var(--ink-muted);">Batch <?= htmlspecialchars($exp['batch_no']) ?></div>
            </div>
            <span class="badge badge-amber"><?= (int)$exp['days_left'] ?> days left</span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <div>
        <div class="eyebrow">RECENT LEDGER</div>
        <h3 style="margin:2px 0 0;font-size:16px;">Latest bills</h3>
      </div>
      <a href="/admin/sales" style="font-size:12.5px;color:var(--green-800);font-weight:600;">All &rarr;</a>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($latestBills as $bill): ?>
        <div onclick="showReceipt(<?= $bill['id'] ?>)" style="display:flex;justify-content:space-between;align-items:center;padding-bottom:8px;border-bottom:1px solid var(--border-warm);cursor:pointer;">
          <div>
            <div class="mono" style="font-weight:600;font-size:13px;"><?= htmlspecialchars($bill['invoice_no']) ?></div>
            <div style="font-size:12px;color:var(--ink-muted);"><?= htmlspecialchars($bill['staff_name']) ?> &middot; <?= $bill['item_count'] ?> items</div>
          </div>
          <span class="mono" style="font-weight:600;">$<?= number_format((float)$bill['total_amount'], 2) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
