/* ==========================================================================
   Dispensa Frontend Interactivity (Vanilla JS)
   ========================================================================== */

function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-backdrop')) {
    e.target.classList.remove('open');
  }
});

// Notification Bell Popover
const bellBtn = document.getElementById('bellBtn');
const notifPopover = document.getElementById('notifPopover');
if (bellBtn && notifPopover) {
  bellBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    notifPopover.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (!notifPopover.contains(e.target) && e.target !== bellBtn) {
      notifPopover.classList.remove('open');
    }
  });
}

const markAllReadBtn = document.getElementById('markAllReadBtn');
if (markAllReadBtn) {
  markAllReadBtn.addEventListener('click', async () => {
    const res = await fetch('/api/notifications/read-all', { method: 'POST' });
    if (res.ok) {
      document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
      const badge = document.getElementById('bellBadge');
      if (badge) badge.remove();
      const cnt = document.getElementById('notifCountText');
      if (cnt) cnt.textContent = '0';
    }
  });
}

// Restock Modal Trigger
document.querySelectorAll('.open-restock-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const stock = btn.dataset.stock;
    const form = document.getElementById('restockForm');
    if (form) {
      form.action = `/admin/inventory/restock/${id}`;
      document.getElementById('restockMedName').textContent = name;
      document.getElementById('restockCurrentQty').textContent = stock;
      document.getElementById('restockUnits').value = 50;
      openModal('restockModal');
    }
  });
});

document.querySelectorAll('.restock-quick').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById('restockUnits');
    if (input) input.value = btn.dataset.val;
  });
});

// 80mm Thermal Receipt Viewer
async function showReceipt(saleId) {
  try {
    const res = await fetch(`/api/receipt/${saleId}`);
    const data = await res.json();
    if (!data.ok || !data.receipt) return;
    renderReceiptModal(data.receipt);
  } catch (err) {
    console.error(err);
  }
}

function renderReceiptModal(r) {
  const meta = document.getElementById('receiptMeta');
  const items = document.getElementById('receiptItems');
  const totals = document.getElementById('receiptTotals');

  meta.innerHTML = `
    <div style="display:flex;justify-content:space-between;"><span>Invoice:</span><strong>${escapeHtml(r.invoice_no)}</strong></div>
    <div style="display:flex;justify-content:space-between;"><span>Date:</span><span>${escapeHtml(r.created_at)}</span></div>
    <div style="display:flex;justify-content:space-between;"><span>Served by:</span><span>${escapeHtml(r.staff_name)}</span></div>
    <div style="display:flex;justify-content:space-between;"><span>Customer:</span><span>${escapeHtml(r.customer_name || 'Walk-in')}</span></div>
    ${r.customer_phone ? `<div style="display:flex;justify-content:space-between;"><span>Phone:</span><span>${escapeHtml(r.customer_phone)}</span></div>` : ''}
  `;

  items.innerHTML = (r.items || []).map(it => `
    <div style="margin-bottom:4px;">
      <div style="display:flex;justify-content:space-between;">
        <span>${escapeHtml(it.medicine_name)} x${it.qty}</span>
        <span>$${Number(it.line_total).toFixed(2)}</span>
      </div>
      <div style="font-size:10.5px;color:#666;">@ $${Number(it.unit_price).toFixed(2)} each</div>
    </div>
  `).join('');

  totals.innerHTML = `
    <div style="display:flex;justify-content:space-between;"><span>Subtotal:</span><span>$${Number(r.subtotal).toFixed(2)}</span></div>
    <div style="display:flex;justify-content:space-between;"><span>Discount (${r.discount_pct}%):</span><span>-$${Number(r.discount_amount).toFixed(2)}</span></div>
    <div style="display:flex;justify-content:space-between;font-weight:700;font-size:14px;margin-top:4px;"><span>TOTAL:</span><span>$${Number(r.total_amount).toFixed(2)}</span></div>
    <div style="display:flex;justify-content:space-between;margin-top:4px;"><span>Paid by:</span><span>${escapeHtml(r.payment_method)}</span></div>
  `;

  openModal('receiptModal');
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
