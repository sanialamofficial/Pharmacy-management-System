<?php
$user = $currentUser;
$isAdmin = $user && $user['role'] === 'admin';
$initials = collect_initials($user['full_name'] ?? 'U');
$unreadCount = 0;
if (!empty($alerts)) {
    foreach ($alerts as $a) {
        if (!$a['read']) {
            $unreadCount++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Dispensa') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <div>
        <a href="<?= $isAdmin ? '/admin' : '/staff' ?>" class="brand-lockup" style="padding:4px 8px;">
          <span class="brand-mark">+</span>
          <span>Dispensa</span>
        </a>

        <nav class="sidebar-nav">
          <div class="eyebrow sidebar-label"><?= $isAdmin ? 'ADMIN CONSOLE' : 'COUNTER' ?></div>

          <?php if ($isAdmin): ?>
            <a href="/admin" class="nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/admin/inventory" class="nav-item <?= ($activeNav ?? '') === 'inventory' ? 'active' : '' ?>">Inventory</a>
            <a href="/admin/staff" class="nav-item <?= ($activeNav ?? '') === 'staff' ? 'active' : '' ?>">Staff</a>
            <a href="/admin/sales" class="nav-item <?= ($activeNav ?? '') === 'sales' ? 'active' : '' ?>">Sales records</a>
            <a href="/admin/checkout" class="nav-item <?= ($activeNav ?? '') === 'checkout' ? 'active' : '' ?>">Counter checkout</a>
            <a href="/admin/settings" class="nav-item <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">Profile &amp; security</a>
          <?php else: ?>
            <a href="/staff" class="nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">My dashboard</a>
            <a href="/staff/checkout" class="nav-item <?= ($activeNav ?? '') === 'checkout' ? 'active' : '' ?>">New bill</a>
            <a href="/staff/sales" class="nav-item <?= ($activeNav ?? '') === 'sales' ? 'active' : '' ?>">My sales</a>
            <a href="/staff/settings" class="nav-item <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">Profile &amp; security</a>
          <?php endif; ?>
        </nav>
      </div>

      <div class="sidebar-footer">
        <div class="user-chip">
          <div class="avatar"><?= htmlspecialchars($initials) ?></div>
          <div style="min-width:0;">
            <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['full_name']) ?></div>
            <div style="font-size:11.5px;color:var(--ink-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['job_title']) ?></div>
          </div>
        </div>
        <a href="/logout" class="btn btn-ghost btn-sm" title="Sign out" style="padding:6px 8px;">&#x21AA;</a>
      </div>
    </aside>

    <div class="main-area">
      <header class="topbar">
        <div style="font-weight:600;color:var(--ink-secondary);font-size:13.5px;">
          <?= date('l, F j') ?>
        </div>

        <div class="topbar-right">
          <?php if ($isAdmin): ?>
            <button type="button" class="bell-btn" id="bellBtn" aria-label="Stock alerts">
              <span>&#128276;</span>
              <?php if ($unreadCount > 0): ?>
                <span class="bell-badge" id="bellBadge"><?= $unreadCount ?></span>
              <?php endif; ?>
            </button>

            <div class="notif-popover" id="notifPopover">
              <div class="notif-header">
                <div>
                  <strong>Stock alerts</strong>
                  <div style="font-size:12px;color:var(--ink-muted);"><span id="notifCountText"><?= $unreadCount ?></span> need attention</div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" id="markAllReadBtn">Mark all read</button>
              </div>
              <div>
                <?php if (empty($alerts)): ?>
                  <div style="padding:18px;text-align:center;color:var(--ink-muted);">All stock &amp; expiry levels look healthy.</div>
                <?php else: ?>
                  <?php foreach ($alerts as $alert): ?>
                    <div class="notif-item <?= $alert['read'] ? '' : 'unread' ?>">
                      <div>
                        <div style="font-size:13px;font-weight:500;"><?= htmlspecialchars($alert['message']) ?></div>
                        <div style="margin-top:4px;display:flex;gap:6px;align-items:center;">
                          <span class="shelf-pill"><?= htmlspecialchars($alert['shelf_location']) ?></span>
                          <span class="badge <?= $alert['type'] === 'low_stock' ? 'badge-terracotta' : 'badge-amber' ?>">
                            <?= $alert['type'] === 'low_stock' ? 'Low stock' : 'Expiring' ?>
                          </span>
                        </div>
                      </div>
                      <?php if ($alert['type'] === 'low_stock'): ?>
                        <button type="button"
                          class="btn btn-outline btn-sm open-restock-btn"
                          data-id="<?= $alert['medicine_id'] ?>"
                          data-name="<?= htmlspecialchars($alert['brand_name']) ?>"
                          data-stock="<?= $alert['stock_qty'] ?>">Restock</button>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <a href="/staff/checkout" class="btn btn-primary btn-sm">+ New bill</a>
          <?php endif; ?>
        </div>
      </header>

      <main class="page-content">
        <?php if (!empty($flash)): ?>
          <div class="flash-banner <?= $flash['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
            <?= htmlspecialchars($flash['message']) ?>
          </div>
        <?php endif; ?>

        <?= $content ?>
      </main>
    </div>
  </div>

  <!-- Global Restock Modal (Admin) -->
  <?php if ($isAdmin): ?>
  <div class="modal-backdrop" id="restockModal">
    <div class="modal-card" style="max-width:420px;">
      <div class="modal-header">
        <h3 class="serif" style="margin:0;font-size:22px;">Restock medicine</h3>
        <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('restockModal')">&times;</button>
      </div>
      <form method="POST" id="restockForm" action="">
        <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/admin/inventory') ?>">
        <div style="margin-bottom:14px;">
          <strong id="restockMedName">Medicine</strong>
          <div style="font-size:12.5px;color:var(--ink-muted);">Current stock: <span id="restockCurrentQty" class="mono">0</span> units</div>
        </div>
        <div class="form-group">
          <label class="form-label" for="restockUnits">Units received</label>
          <input class="form-control mono" type="number" id="restockUnits" name="units" value="50" min="1" required>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
          <button type="button" class="pill-filter restock-quick" data-val="20">+20</button>
          <button type="button" class="pill-filter restock-quick" data-val="50">+50</button>
          <button type="button" class="pill-filter restock-quick" data-val="100">+100</button>
          <button type="button" class="pill-filter restock-quick" data-val="200">+200</button>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('restockModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add to stock</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Global 80mm Thermal Receipt Modal -->
  <div class="modal-backdrop" id="receiptModal">
    <div class="modal-card" style="max-width:420px;">
      <div class="modal-header no-print">
        <h3 class="serif" style="margin:0;font-size:20px;">Receipt preview</h3>
        <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('receiptModal')">&times;</button>
      </div>
      <div class="thermal-receipt" id="thermalReceiptBody">
        <div style="text-align:center;">
          <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($config['pharmacy_name']) ?></div>
          <div><?= htmlspecialchars($config['pharmacy_address']) ?></div>
          <div>License: <?= htmlspecialchars($config['pharmacy_license']) ?></div>
        </div>
        <div class="thermal-divider"></div>
        <div id="receiptMeta"></div>
        <div class="thermal-divider"></div>
        <div id="receiptItems"></div>
        <div class="thermal-divider"></div>
        <div id="receiptTotals"></div>
        <div class="thermal-divider"></div>
        <div style="text-align:center;font-size:11px;color:#555;">
          Thank you for choosing <?= htmlspecialchars($config['pharmacy_name']) ?>.<br>
          Prescription items dispensed per pharmacist verification.
        </div>
      </div>
      <div class="no-print" style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('receiptModal')">Close</button>
        <button type="button" class="btn btn-primary" onclick="window.print()">Reprint receipt</button>
      </div>
    </div>
  </div>

  <script src="/assets/js/app.js"></script>
</body>
</html>
