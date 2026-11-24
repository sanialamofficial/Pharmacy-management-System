<?php
$basePath = $isStaffView ? '/staff/sales' : '/admin/sales';
$exportQuery = http_build_query($filters);
?>

<div class="page-header">
  <div>
    <div class="eyebrow">RECORDS</div>
    <h1 class="page-title"><?= $isStaffView ? 'My sales' : 'Sales records' ?></h1>
  </div>
  <a href="<?= $basePath ?>/export?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-outline">Export CSV</a>
</div>

<div class="kpi-grid-3">
  <div class="card">
    <div class="eyebrow">Bills</div>
    <div class="kpi-val"><?= number_format($summary['bills']) ?></div>
  </div>
  <div class="card">
    <div class="eyebrow">Revenue</div>
    <div class="kpi-val">$<?= number_format($summary['revenue'], 2) ?></div>
  </div>
  <div class="card">
    <div class="eyebrow">Average bill</div>
    <div class="kpi-val">$<?= number_format($summary['avg_bill'], 2) ?></div>
  </div>
</div>

<div class="card">
  <form method="GET" action="<?= $basePath ?>" class="filter-bar">
    <input type="text" name="q" class="form-control" style="max-width:260px;" placeholder="Invoice no., customer or medicine" value="<?= htmlspecialchars($filters['q']) ?>">

    <?php if (!$isStaffView): ?>
      <select name="user_id" class="form-control" style="max-width:190px;" onchange="this.form.submit()">
        <option value="">All staff</option>
        <?php foreach ($staffFilterList as $sf): ?>
          <option value="<?= $sf['id'] ?>" <?= (string)$filters['user_id'] === (string)$sf['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sf['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>

    <select name="range" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
      <option value="today" <?= $filters['range'] === 'today' ? 'selected' : '' ?>>Today</option>
      <option value="7d" <?= $filters['range'] === '7d' ? 'selected' : '' ?>>Last 7 days</option>
      <option value="30d" <?= $filters['range'] === '30d' ? 'selected' : '' ?>>Last 30 days</option>
      <option value="all" <?= $filters['range'] === 'all' ? 'selected' : '' ?>>All time</option>
    </select>

    <select name="payment" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
      <option value="All" <?= $filters['payment'] === 'All' ? 'selected' : '' ?>>Any payment</option>
      <option value="Cash" <?= $filters['payment'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
      <option value="Card" <?= $filters['payment'] === 'Card' ? 'selected' : '' ?>>Card</option>
      <option value="Mobile" <?= $filters['payment'] === 'Mobile' ? 'selected' : '' ?>>Mobile</option>
    </select>

    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Invoice</th>
          <th>Date</th>
          <?php if (!$isStaffView): ?><th>Served by</th><?php endif; ?>
          <th>Customer</th>
          <th>Items</th>
          <th>Payment</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($sales)): ?>
          <tr><td colspan="<?= $isStaffView ? 6 : 7 ?>" style="text-align:center;color:var(--ink-muted);padding:28px;">No sales records found.</td></tr>
        <?php else: ?>
          <?php foreach ($sales as $s):
            $payClass = match ($s['payment_method']) {
                'Card' => 'badge-amber',
                'Mobile' => 'badge-green',
                default => 'badge-neutral',
            };
          ?>
            <tr class="clickable" onclick="showReceipt(<?= $s['id'] ?>)">
              <td class="mono" style="font-weight:600;"><?= htmlspecialchars($s['invoice_no']) ?></td>
              <td style="font-size:12.5px;color:var(--ink-secondary);"><?= date('M j, g:i A', strtotime($s['created_at'])) ?></td>
              <?php if (!$isStaffView): ?><td><?= htmlspecialchars($s['staff_name']) ?></td><?php endif; ?>
              <td><?= htmlspecialchars($s['customer_name'] ?: 'Walk-in') ?></td>
              <td class="mono"><?= (int)$s['item_count'] ?></td>
              <td>
                <span class="badge <?= $payClass ?>"><?= htmlspecialchars($s['payment_method']) ?></span>
                <?php if ((int)$s['discount_pct'] > 0): ?>
                  <span class="mono" style="font-size:11.5px;color:var(--ink-muted);margin-left:4px;">-<?= (int)$s['discount_pct'] ?>%</span>
                <?php endif; ?>
              </td>
              <td class="mono" style="text-align:right;font-weight:600;">$<?= number_format((float)$s['total_amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
