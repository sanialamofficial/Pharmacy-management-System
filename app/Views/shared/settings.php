<?php
$initials = collect_initials($profile['full_name']);
?>

<div class="page-header">
  <div>
    <div class="eyebrow">ACCOUNT</div>
    <h1 class="page-title">Profile &amp; security</h1>
  </div>
</div>

<div class="grid-2-1">
  <div style="display:flex;flex-direction:column;gap:18px;">
    <div class="card">
      <h3 style="margin:0 0 14px;font-size:16px;">Profile details</h3>
      <form method="POST" action="/settings/profile" id="profileForm">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Job title</label>
            <input class="form-control" name="job_title" value="<?= htmlspecialchars($profile['job_title']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" value="<?= htmlspecialchars($profile['phone']) ?>">
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:6px;">
          <button type="reset" class="btn btn-outline">Discard</button>
          <button type="submit" class="btn btn-primary">Save profile</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h3 style="margin:0 0 14px;font-size:16px;">Change password</h3>
      <form method="POST" action="/settings/password">
        <div class="form-group">
          <label class="form-label">Current password</label>
          <input class="form-control" type="password" name="current_password" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">New password</label>
            <input class="form-control" type="password" name="new_password" id="newPwdInput" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm new password</label>
            <input class="form-control" type="password" name="confirm_password" required>
          </div>
        </div>

        <div class="strength-bars" id="strengthBars">
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
        </div>

        <ul style="list-style:none;padding:0;margin:8px 0 16px;font-size:12.5px;color:var(--ink-muted);display:flex;flex-direction:column;gap:4px;">
          <li id="ruleLen">&#9675; At least 8 characters</li>
          <li id="ruleNum">&#9675; Contains a number</li>
          <li id="ruleCase">&#9675; Upper and lower case</li>
        </ul>

        <div style="display:flex;justify-content:flex-end;">
          <button type="submit" class="btn btn-primary">Update password</button>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="card">
      <div style="display:flex;gap:14px;align-items:center;margin-bottom:14px;">
        <div class="avatar lg"><?= htmlspecialchars($initials) ?></div>
        <div>
          <strong style="font-size:16px;"><?= htmlspecialchars($profile['full_name']) ?></strong>
          <div style="font-size:13px;color:var(--ink-muted);"><?= htmlspecialchars($profile['job_title']) ?></div>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
        <span class="badge <?= $profile['role'] === 'admin' ? 'badge-terracotta' : 'badge-green' ?>"><?= ucfirst($profile['role']) ?></span>
        <span class="badge badge-neutral"><?= htmlspecialchars($profile['shift']) ?></span>
      </div>
      <div style="font-size:12.5px;color:var(--ink-muted);padding-top:12px;border-top:1px solid var(--border-warm);">
        Member since <?= date('F j, Y', strtotime($profile['joined_at'])) ?>
      </div>
    </div>
  </div>
</div>

<script>
  const newPwd = document.getElementById('newPwdInput');
  const segs = document.querySelectorAll('#strengthBars .strength-seg');
  const rLen = document.getElementById('ruleLen');
  const rNum = document.getElementById('ruleNum');
  const rCase = document.getElementById('ruleCase');

  newPwd.addEventListener('input', () => {
    const v = newPwd.value;
    const hasLen = v.length >= 8;
    const hasNum = /\d/.test(v);
    const hasCase = /[a-z]/.test(v) && /[A-Z]/.test(v);

    rLen.innerHTML = `${hasLen ? '&#10003;' : '&#9675;'} At least 8 characters`;
    rLen.style.color = hasLen ? 'var(--mint-text)' : 'var(--ink-muted)';

    rNum.innerHTML = `${hasNum ? '&#10003;' : '&#9675;'} Contains a number`;
    rNum.style.color = hasNum ? 'var(--mint-text)' : 'var(--ink-muted)';

    rCase.innerHTML = `${hasCase ? '&#10003;' : '&#9675;'} Upper and lower case`;
    rCase.style.color = hasCase ? 'var(--mint-text)' : 'var(--ink-muted)';

    let score = (hasLen ? 1 : 0) + (hasNum ? 1 : 0) + (hasCase ? 1 : 0) + (v.length >= 12 ? 1 : 0);
    segs.forEach((s, i) => {
      s.className = 'strength-seg';
      if (i < score) {
        s.classList.add(score <= 1 ? 'active-weak' : (score <= 2 ? 'active-med' : 'active-strong'));
      }
    });
  });
</script>
