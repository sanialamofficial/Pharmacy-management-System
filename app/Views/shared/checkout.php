<div class="page-header">
  <div>
    <div class="eyebrow">POINT OF SALE</div>
    <h1 class="page-title"><?= $currentUser['role'] === 'admin' ? 'Counter checkout' : 'New bill' ?></h1>
  </div>
  <span class="badge badge-neutral">Press <code class="mono">/</code> to search</span>
</div>

<div class="pos-layout">
  <!-- Left Catalog -->
  <div class="card">
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
      <input type="text" id="posSearchInput" class="form-control" placeholder="Search brand, generic, or shelf...">
      <label style="display:flex;align-items:center;gap:6px;white-space:nowrap;font-size:12.5px;font-weight:600;cursor:pointer;">
        <input type="checkbox" id="posInStockOnly" checked>
        <span>In stock only</span>
      </label>
    </div>

    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;" id="posCategoryPills">
      <button type="button" class="pill-filter active" data-cat="All">All</button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="pill-filter" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
      <?php endforeach; ?>
    </div>

    <div style="font-size:12px;color:var(--ink-muted);margin-bottom:10px;"><span id="posMatchCount">0</span> matches</div>

    <div class="pos-catalog-list" id="posCatalogList"></div>
  </div>

  <!-- Right Current Bill -->
  <div class="card" style="position:sticky;top:82px;">
    <h3 class="serif" style="margin:0 0 14px;font-size:20px;">Current bill</h3>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
      <input type="text" id="billCustomerName" class="form-control" placeholder="Customer name (optional)">
      <input type="text" id="billCustomerPhone" class="form-control" placeholder="Phone (optional)">
    </div>

    <div id="billCartItems" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;max-height:260px;overflow-y:auto;">
      <div style="padding:20px;text-align:center;color:var(--ink-muted);border:1px dashed var(--border-warm);border-radius:8px;">
        Tap <strong>+ Add</strong> on any medicine to start the bill.
      </div>
    </div>

    <div id="rxWarningBox" class="rx-warning-box" style="display:none;">
      <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-weight:600;">
        <input type="checkbox" id="rxCheckedInput" style="margin-top:3px;">
        <span>
          <strong>Prescription checked</strong><br>
          <span style="font-weight:400;">This bill contains prescription-only medicine.</span>
        </span>
      </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
      <div>
        <div class="eyebrow" style="margin-bottom:6px;">DISCOUNT</div>
        <select id="billDiscount" class="form-control">
          <option value="0">0%</option>
          <option value="5">5%</option>
          <option value="10">10%</option>
          <option value="15">15%</option>
        </select>
      </div>
      <div>
        <div class="eyebrow" style="margin-bottom:6px;">PAYMENT</div>
        <select id="billPayment" class="form-control">
          <option value="Cash">Cash</option>
          <option value="Card">Card</option>
          <option value="Mobile">Mobile</option>
        </select>
      </div>
    </div>

    <div style="border-top:1px solid var(--border-warm);padding-top:12px;margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span style="color:var(--ink-secondary);">Subtotal</span>
        <span class="mono" id="billSubtotal">$0.00</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;">
        <span>Total</span>
        <span class="mono" id="billTotal">$0.00</span>
      </div>
    </div>

    <button type="button" id="generateBillBtn" class="btn btn-primary" style="width:100%;" disabled>Generate bill</button>
  </div>
</div>

<script>
  const CATALOG = <?= json_encode($medicines, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  let activeCategory = 'All';
  const cart = new Map(); // id -> { med, qty }

  const searchEl = document.getElementById('posSearchInput');
  const inStockEl = document.getElementById('posInStockOnly');
  const catalogListEl = document.getElementById('posCatalogList');
  const matchCountEl = document.getElementById('posMatchCount');

  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      e.preventDefault();
      searchEl.focus();
    }
  });

  document.querySelectorAll('#posCategoryPills .pill-filter').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#posCategoryPills .pill-filter').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeCategory = btn.dataset.cat;
      renderCatalog();
    });
  });

  searchEl.addEventListener('input', renderCatalog);
  inStockEl.addEventListener('change', renderCatalog);
  document.getElementById('billDiscount').addEventListener('change', renderCart);
  document.getElementById('rxCheckedInput').addEventListener('change', updateSubmitState);

  function renderCatalog() {
    const q = searchEl.value.trim().toLowerCase();
    const onlyInStock = inStockEl.checked;

    const filtered = CATALOG.filter(m => {
      if (onlyInStock && Number(m.stock_qty) <= 0) return false;
      if (activeCategory !== 'All' && m.category !== activeCategory) return false;
      if (q) {
        const hay = `${m.brand_name} ${m.generic_name} ${m.strength} ${m.shelf_location}`.toLowerCase();
        if (!hay.includes(q)) return false;
      }
      return true;
    });

    matchCountEl.textContent = filtered.length;

    catalogListEl.innerHTML = filtered.map(m => {
      const id = Number(m.id);
      const stock = Number(m.stock_qty);
      const inCart = cart.get(id);
      const isLow = stock < <?= (int)$config['low_stock_threshold'] ?>;

      const actionHtml = stock <= 0
        ? `<span class="badge badge-terracotta">Out of stock</span>`
        : inCart
          ? `<div class="qty-stepper">
               <button type="button" onclick="adjustCart(${id}, -1)">-</button>
               <span>${inCart.qty}</span>
               <button type="button" onclick="adjustCart(${id}, 1)">+</button>
             </div>`
          : `<button type="button" class="btn btn-outline btn-sm" onclick="adjustCart(${id}, 1)">+ Add</button>`;

      return `
        <div class="pos-item-row">
          <div>
            <div style="font-weight:600;">
              ${escapeHtml(m.brand_name)} ${escapeHtml(m.strength)}
              ${Number(m.rx_required) === 1 ? '<span class="badge-rx" style="margin-left:4px;">Rx</span>' : ''}
            </div>
            <div style="font-size:12px;color:var(--ink-muted);">
              ${escapeHtml(m.generic_name)} &middot; <span class="shelf-pill">${escapeHtml(m.shelf_location)}</span>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="text-align:right;">
              <div class="mono" style="font-weight:600;">$${Number(m.unit_price).toFixed(2)}</div>
              <div style="font-size:11.5px;color:${isLow ? 'var(--terracotta)' : 'var(--ink-muted)'};">${stock} left</div>
            </div>
            ${actionHtml}
          </div>
        </div>
      `;
    }).join('') || '<div style="padding:20px;text-align:center;color:var(--ink-muted);">No matching medicines.</div>';
  }

  function adjustCart(id, delta) {
    const med = CATALOG.find(m => Number(m.id) === id);
    if (!med) return;
    const maxStock = Number(med.stock_qty);
    const existing = cart.get(id);
    const nextQty = (existing ? existing.qty : 0) + delta;

    if (nextQty <= 0) {
      cart.delete(id);
    } else if (nextQty <= maxStock) {
      cart.set(id, { med, qty: nextQty });
    }
    renderCatalog();
    renderCart();
  }

  function renderCart() {
    const cartEl = document.getElementById('billCartItems');
    if (cart.size === 0) {
      cartEl.innerHTML = `<div style="padding:20px;text-align:center;color:var(--ink-muted);border:1px dashed var(--border-warm);border-radius:8px;">Tap <strong>+ Add</strong> on any medicine to start the bill.</div>`;
      document.getElementById('rxWarningBox').style.display = 'none';
      document.getElementById('rxCheckedInput').checked = false;
      document.getElementById('billSubtotal').textContent = '$0.00';
      document.getElementById('billTotal').textContent = '$0.00';
      updateSubmitState();
      return;
    }

    let subtotal = 0;
    let hasRx = false;
    const rows = [];

    cart.forEach(({ med, qty }) => {
      const line = Number(med.unit_price) * qty;
      subtotal += line;
      if (Number(med.rx_required) === 1) hasRx = true;
      rows.push(`
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:var(--bg-surface);border:1px solid var(--border-warm);border-radius:8px;">
          <div>
            <div style="font-weight:600;font-size:13px;">${escapeHtml(med.brand_name)} ${escapeHtml(med.strength)}</div>
            <div class="mono" style="font-size:11.5px;color:var(--ink-muted);">$${Number(med.unit_price).toFixed(2)} &times; ${qty} = $${line.toFixed(2)}</div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <div class="qty-stepper">
              <button type="button" onclick="adjustCart(${med.id}, -1)">-</button>
              <span>${qty}</span>
              <button type="button" onclick="adjustCart(${med.id}, 1)">+</button>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--terracotta);padding:4px 6px;" onclick="adjustCart(${med.id}, -9999)">&times;</button>
          </div>
        </div>
      `);
    });

    cartEl.innerHTML = rows.join('');
    document.getElementById('rxWarningBox').style.display = hasRx ? 'block' : 'none';
    if (!hasRx) document.getElementById('rxCheckedInput').checked = false;

    const discountPct = Number(document.getElementById('billDiscount').value || 0);
    const total = subtotal * (1 - discountPct / 100);
    document.getElementById('billSubtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('billTotal').textContent = `$${total.toFixed(2)}`;
    updateSubmitState();
  }

  function updateSubmitState() {
    const btn = document.getElementById('generateBillBtn');
    if (cart.size === 0) {
      btn.disabled = true;
      return;
    }
    let hasRx = false;
    cart.forEach(({ med }) => { if (Number(med.rx_required) === 1) hasRx = true; });
    btn.disabled = hasRx && !document.getElementById('rxCheckedInput').checked;
  }

  document.getElementById('generateBillBtn').addEventListener('click', async () => {
    const items = [];
    cart.forEach(({ med, qty }) => items.push({ id: Number(med.id), qty }));

    const payload = {
      customer_name: document.getElementById('billCustomerName').value.trim(),
      customer_phone: document.getElementById('billCustomerPhone').value.trim(),
      discount_pct: Number(document.getElementById('billDiscount').value),
      payment_method: document.getElementById('billPayment').value,
      rx_checked: document.getElementById('rxCheckedInput').checked,
      items
    };

    const btn = document.getElementById('generateBillBtn');
    btn.disabled = true;
    btn.textContent = 'Generating...';

    try {
      const res = await fetch('/checkout/complete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        alert(data.error || 'Could not complete checkout.');
        updateSubmitState();
        btn.textContent = 'Generate bill';
        return;
      }

      // Decrement local catalog stock & reset cart
      items.forEach(it => {
        const m = CATALOG.find(c => Number(c.id) === it.id);
        if (m) m.stock_qty = Number(m.stock_qty) - it.qty;
      });
      cart.clear();
      document.getElementById('billCustomerName').value = '';
      document.getElementById('billCustomerPhone').value = '';
      renderCatalog();
      renderCart();
      btn.textContent = 'Generate bill';
      renderReceiptModal(data.receipt);
    } catch (err) {
      alert('Network error completing checkout.');
      btn.textContent = 'Generate bill';
      updateSubmitState();
    }
  });

  renderCatalog();
</script>
