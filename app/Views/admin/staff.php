<div class="page-header">
  <div>
    <div class="eyebrow">TEAM</div>
    <h1 class="page-title">Counter staff</h1>
  </div>
  <button type="button" class="btn btn-primary" onclick="openModal('addStaffModal')">+ Add staff</button>
</div>

<div class="staff-grid">
  <?php foreach ($staffList as $st):
    $initials = collect_initials($st['full_name']);
    $isSuspended = $st['status'] === 'suspended';
  ?>
    <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;gap:14px;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div style="display:flex;gap:12px;align-items:center;">
            <div class="avatar"><?= htmlspecialchars($initials) ?></div>
            <div>
              <strong><?= htmlspecialchars($st['full_name']) ?></strong>
              <div style="font-size:12px;color:var(--ink-muted);"><?= htmlspecialchars($st['job_title']) ?> &middot; <?= htmlspecialchars($st['shift']) ?></div>
            </div>
          </div>
          <span class="badge <?= $isSuspended ? 'badge-terracotta' : 'badge-green' ?>">
            <?= $isSuspended ? 'Suspended' : 'Active' ?>
          </span>
        </div>

        <div style="margin-top:12px;font-size:12.5px;color:var(--ink-secondary);">
          <div><?= htmlspecialchars($st['email']) ?></div>
          <div><?= htmlspecialchars($st['phone'] ?: 'No phone') ?></div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:14px;padding:10px;background:var(--bg-surface);border-radius:8px;border:1px solid var(--border-warm);">
          <div>
            <div style="font-size:11px;color:var(--ink-muted);">Bills &middot; 30d</div>
            <strong class="mono"><?= (int)$st['bills_30d'] ?></strong>
          </div>
          <div>
            <div style="font-size:11px;color:var(--ink-muted);">Revenue</div>
            <strong class="mono">$<?= number_format((float)$st['revenue_30d'], 0) ?></strong>
          </div>
          <div>
            <div style="font-size:11px;color:var(--ink-muted);">Last sale</div>
            <strong style="font-size:12px;"><?= $st['last_sale_at'] ? date('M j', strtotime($st['last_sale_at'])) : '—' ?></strong>
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid var(--border-warm);">
        <span style="font-size:11.5px;color:var(--ink-muted);">Joined <?= date('M j, Y', strtotime($st['joined_at'])) ?></span>
        <div style="display:flex;gap:6px;">
          <form method="POST" action="/admin/staff/toggle/<?= $st['id'] ?>" style="display:inline;">
            <button type="submit" class="btn btn-outline btn-sm"><?= $isSuspended ? 'Reactivate' : 'Suspend' ?></button>
          </form>
          <button type="button" class="btn btn-ghost btn-sm" title="Reset password" onclick="openResetModal(<?= $st['id'] ?>, '<?= htmlspecialchars($st['full_name'], ENT_QUOTES) ?>')">&#128273;</button>
          <form method="POST" action="/admin/staff/delete/<?= $st['id'] ?>" style="display:inline;" onsubmit="return confirm('Remove this staff member?');">
            <button type="submit" class="btn btn-ghost btn-sm" title="Delete" style="color:var(--terracotta);">&#128465;</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <button type="button" class="add-staff-card" onclick="openModal('addStaffModal')">
    <span style="font-size:28px;margin-bottom:6px;">+</span>
    <strong>Add a staff member</strong>
  </button>
</div>

<!-- Add Staff Modal -->
<div class="modal-backdrop" id="addStaffModal">
  <div class="modal-card" style="max-width:460px;">
    <div class="modal-header">
      <h3 class="serif" style="margin:0;font-size:22px;">Add a staff member</h3>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('addStaffModal')">&times;</button>
    </div>
    <form method="POST" action="/admin/staff/store">
      <div class="form-group">
        <label class="form-label">Full name</label>
        <input class="form-control" name="full_name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Work email</label>
        <input class="form-control" type="email" name="email" placeholder="name@greenleaf.rx" required>
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input class="form-control" name="phone" placeholder="+1 (555) 210-0000">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Role title</label>
          <select class="form-control" name="job_title">
            <option>Counter Pharmacist</option>
            <option>Senior Counter Pharmacist</option>
            <option>Pharmacy Dispenser</option>
            <option>Pharmacy Technician</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Shift</label>
          <select class="form-control" name="shift">
            <option>Morning (8a-4p)</option>
            <option>Evening (1p-9p)</option>
            <option>Day (9a-6p)</option>
          </select>
        </div>
      </div>
      <div style="font-size:12px;color:var(--ink-muted);margin-bottom:14px;">Default demo password will be set to <code class="mono">greenleaf</code>.</div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('addStaffModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create account</button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal-backdrop" id="resetPwdModal">
  <div class="modal-card" style="max-width:400px;">
    <div class="modal-header">
      <h3 class="serif" style="margin:0;font-size:20px;">Reset password</h3>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('resetPwdModal')">&times;</button>
    </div>
    <form method="POST" id="resetPwdForm" action="">
      <p style="margin-top:0;color:var(--ink-secondary);">Set a new password for <strong id="resetStaffName"></strong>.</p>
      <div class="form-group">
        <label class="form-label">New password</label>
        <input class="form-control" type="text" name="new_password" value="greenleaf" required minlength="6">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn btn-outline" onclick="closeModal('resetPwdModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Update password</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openResetModal(id, name) {
    document.getElementById('resetPwdForm').action = `/admin/staff/reset-password/${id}`;
    document.getElementById('resetStaffName').textContent = name;
    openModal('resetPwdModal');
  }
</script>
