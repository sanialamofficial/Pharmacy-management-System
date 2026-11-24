<div class="page-header">
  <div>
    <div class="eyebrow">INVENTORY</div>
    <h1 class="page-title">Medicine stock</h1>
  </div>
  <button type="button" class="btn btn-primary" onclick="openAddMedicineModal()">+ Add medicine</button>
</div>

<div class="kpi-grid-3">
  <div class="card">
    <div class="eyebrow">Products listed</div>
    <div class="kpi-val"><?= number_format($summary['total_products']) ?></div>
  </div>
  <div class="card">
    <div class="eyebrow">Below threshold (&lt; <?= $config['low_stock_threshold'] ?>)</div>
    <div class="kpi-val" style="color:var(--terracotta);"><?= number_format($summary['below_threshold']) ?></div>
  </div>
  <div class="card">
    <div class="eyebrow">Stock value at retail</div>
    <div class="kpi-val">$<?= number_format($summary['retail_value'], 2) ?></div>
  </div>
</div>

<div class="card">
  <form method="GET" action="/admin/inventory" class="filter-bar">
    <input type="text" name="q" class="form-control" style="max-width:280px;" placeholder="Search name, generic, batch, shelf" value="<?= htmlspecialchars($filters['q']) ?>">
    <select name="category" class="form-control" style="max-width:210px;" onchange="this.form.submit()">
      <option value="All">All categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="quick" id="invQuickInput" value="<?= htmlspecialchars($filters['quick']) ?>">
    <button type="button" class="pill-filter <?= $filters['quick'] === 'all' ? 'active' : '' ?>" onclick="setInvQuick('all')">All</button>
    <button type="button" class="pill-filter <?= $filters['quick'] === 'low' ? 'active' : '' ?>" onclick="setInvQuick('low')">Low stock</button>
    <button type="button" class="pill-filter <?= $filters['quick'] === 'expiring' ? 'active' : '' ?>" onclick="setInvQuick('expiring')">Expiring</button>
    <button type="button" class="pill-filter <?= $filters['quick'] === 'rx' ? 'active' : '' ?>" onclick="setInvQuick('rx')">Rx only</button>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Category</th>
          <th>Batch &middot; Expiry</th>
          <th>Shelf</th>
          <th>Price</th>
          <th>Stock</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($medicines)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--ink-muted);padding:28px;">No medicines match the current filters.</td></tr>
        <?php else: ?>
          <?php foreach ($medicines as $m):
            $isLow = (int)$m['stock_qty'] < $config['low_stock_threshold'];
            $daysExp = (int)$m['days_to_expiry'];
            $isExpiring = $daysExp <= $config['expiry_warning_days'];
          ?>
            <tr>
              <td>
                <div style="font-weight:600;">
                  <?= htmlspecialchars($m['brand_name'] . ' ' . $m['strength']) ?>
                  <?php if ((int)$m['rx_required'] === 1): ?>
                    <span class="badge-rx" style="margin-left:4px;">Rx</span>
                  <?php endif; ?>
                </div>
                <div style="font-size:12px;color:var(--ink-muted);">
                  <?= htmlspecialchars($m['generic_name']) ?> &middot; <?= htmlspecialchars($m['form']) ?> &middot; <?= htmlspecialchars($m['manufacturer']) ?>
                </div>
              </td>
              <td><span class="badge badge-neutral"><?= htmlspecialchars($m['category']) ?></span></td>
              <td>
                <div class="mono" style="font-size:12.5px;"><?= htmlspecialchars($m['batch_no']) ?></div>
                <div style="font-size:12px;color:<?= $isExpiring ? 'var(--amber)' : 'var(--ink-muted)' ?>;">
                  <?= htmlspecialchars($m['expiry_date']) ?>
                  <?php if ($isExpiring): ?>
                    (<?= $daysExp ?> days left)
                  <?php endif; ?>
                </div>
              </td>
              <td><span class="shelf-pill"><?= htmlspecialchars($m['shelf_location']) ?></span></td>
              <td class="mono">$<?= number_format((float)$m['unit_price'], 2) ?></td>
              <td>
                <span class="mono" style="font-weight:700;"><?= (int)$m['stock_qty'] ?></span>
                <?php if ($isLow): ?>
                  <span class="badge badge-terracotta" style="margin-left:4px;">Low</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right;white-space:nowrap;">
                <button type="button" class="btn btn-outline btn-sm open-restock-btn"
                  data-id="<?= $m['id'] ?>"
                  data-name="<?= htmlspecialchars($m['brand_name'] . ' ' . $m['strength']) ?>"
                  data-stock="<?= $m['stock_qty'] ?>">Restock</button>
                <button type="button" class="btn btn-ghost btn-sm"
                  onclick='openEditMedicineModal(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                <form method="POST" action="/admin/inventory/delete/<?= $m['id'] ?>" style="display:inline;" onsubmit="return confirm('Delete this medicine?');">
                  <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--terracotta);">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Medicine Modal -->
<div class="modal-backdrop" id="medicineModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3 class="serif" style="margin:0;font-size:22px;" id="medModalTitle">Add a new medicine</h3>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('medicineModal')">&times;</button>
    </div>
    <form method="POST" id="medicineForm" action="/admin/inventory/store">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Brand name</label>
          <input class="form-control" name="brand_name" id="mBrand" required>
        </div>
        <div class="form-group">
          <label class="form-label">Generic name</label>
          <input class="form-control" name="generic_name" id="mGeneric" required>
        </div>
        <div class="form-group">
          <label class="form-label">Strength</label>
          <input class="form-control" name="strength" id="mStrength" placeholder="500 mg" required>
        </div>
        <div class="form-group">
          <label class="form-label">Form</label>
          <select class="form-control" name="form" id="mForm">
            <?php foreach ($forms as $f): ?>
              <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-control" name="category" id="mCategory">
            <?php foreach ($categories as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Manufacturer</label>
          <input class="form-control" name="manufacturer" id="mMfg" required>
        </div>
        <div class="form-group">
          <label class="form-label">Shelf location</label>
          <input class="form-control" name="shelf_location" id="mShelf" placeholder="Shelf A1" required>
        </div>
        <div class="form-group">
          <label class="form-label">Unit price ($)</label>
          <input class="form-control mono" type="number" step="0.01" min="0" name="unit_price" id="mPrice" required>
        </div>
        <div class="form-group">
          <label class="form-label">Units in stock <span style="color:var(--ink-muted);font-weight:400;">(Alerts below 5)</span></label>
          <input class="form-control mono" type="number" min="0" name="stock_qty" id="mStock" required>
        </div>
        <div class="form-group">
          <label class="form-label">Batch no.</label>
          <input class="form-control mono" name="batch_no" id="mBatch" required>
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">Expiry date</label>
          <input class="form-control" type="date" name="expiry_date" id="mExpiry" required>
        </div>
      </div>
      <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-weight:600;cursor:pointer;">
        <input type="checkbox" name="rx_required" id="mRx" value="1">
        <span>Prescription required (Rx)</span>
      </label>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('medicineModal')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="medModalSubmit">Save medicine</button>
      </div>
    </form>
  </div>
</div>

<script>
  function setInvQuick(val) {
    document.getElementById('invQuickInput').value = val;
    document.getElementById('invQuickInput').form.submit();
  }
  function openAddMedicineModal() {
    document.getElementById('medModalTitle').textContent = 'Add a new medicine';
    document.getElementById('medicineForm').action = '/admin/inventory/store';
    document.getElementById('medicineForm').reset();
    openModal('medicineModal');
  }
  function openEditMedicineModal(m) {
    document.getElementById('medModalTitle').textContent = 'Edit medicine';
    document.getElementById('medicineForm').action = `/admin/inventory/update/${m.id}`;
    document.getElementById('mBrand').value = m.brand_name;
    document.getElementById('mGeneric').value = m.generic_name;
    document.getElementById('mStrength').value = m.strength;
    document.getElementById('mForm').value = m.form;
    document.getElementById('mCategory').value = m.category;
    document.getElementById('mMfg').value = m.manufacturer;
    document.getElementById('mShelf').value = m.shelf_location;
    document.getElementById('mPrice').value = m.unit_price;
    document.getElementById('mStock').value = m.stock_qty;
    document.getElementById('mBatch').value = m.batch_no;
    document.getElementById('mExpiry').value = m.expiry_date;
    document.getElementById('mRx').checked = Number(m.rx_required) === 1;
    openModal('medicineModal');
  }
</script>
