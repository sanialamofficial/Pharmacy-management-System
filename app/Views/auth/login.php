<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <div class="login-split">
    <section class="login-left">
      <div>
        <a href="/" class="brand-lockup light">
          <span class="brand-mark">+</span>
          <span>Dispensa</span>
        </a>
      </div>

      <div>
        <h1 class="login-headline">Good to see you back behind the counter.</h1>
      </div>

      <div style="font-size:13px;opacity:0.85;">
        <?= htmlspecialchars($config['pharmacy_name']) ?> &middot; <?= htmlspecialchars($config['pharmacy_address']) ?>
      </div>
    </section>

    <section class="login-right">
      <div class="login-box">
        <div class="eyebrow">SIGN IN</div>
        <h2 class="serif" style="font-size:28px;margin:6px 0 18px;">Access your workspace</h2>

        <?php if (!empty($error)): ?>
          <div class="flash-banner flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" id="loginForm">
          <div class="form-group">
            <label class="form-label" for="email">Work email</label>
            <input class="form-control" type="email" id="email" name="email" placeholder="rehana@greenleaf.rx" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div style="position:relative;">
              <input class="form-control" type="password" id="password" name="password" placeholder="Enter password" required style="padding-right:64px;">
              <button type="button" id="togglePwdBtn" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--ink-muted);cursor:pointer;font-size:12px;font-weight:600;">Show</button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;margin-top:6px;">Sign in &rarr;</button>
        </form>

        <div class="demo-accounts-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
            <span class="eyebrow">DEMO ACCOUNTS</span>
            <span class="mono" style="font-size:11.5px;color:var(--ink-muted);">password: greenleaf</span>
          </div>

          <button type="button" class="demo-account-btn" data-email="rehana@greenleaf.rx">
            <div>
              <strong>Rehana Kabir</strong>
              <div style="font-size:12px;color:var(--ink-muted);">rehana@greenleaf.rx</div>
            </div>
            <span class="badge badge-terracotta">Admin</span>
          </button>

          <button type="button" class="demo-account-btn" data-email="tomas@greenleaf.rx">
            <div>
              <strong>Tom&aacute;s Okafor</strong>
              <div style="font-size:12px;color:var(--ink-muted);">tomas@greenleaf.rx</div>
            </div>
            <span class="badge badge-green">Staff</span>
          </button>

          <button type="button" class="demo-account-btn" data-email="priya@greenleaf.rx">
            <div>
              <strong>Priya Venkataraman</strong>
              <div style="font-size:12px;color:var(--ink-muted);">priya@greenleaf.rx</div>
            </div>
            <span class="badge badge-green">Staff</span>
          </button>

          <button type="button" class="demo-account-btn" data-email="elin@greenleaf.rx">
            <div>
              <strong>Elin Marsh</strong>
              <div style="font-size:12px;color:var(--ink-muted);">elin@greenleaf.rx</div>
            </div>
            <span class="badge badge-green">Staff</span>
          </button>
        </div>
      </div>
    </section>
  </div>

  <script>
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePwdBtn');
    toggleBtn.addEventListener('click', () => {
      const isPwd = pwdInput.type === 'password';
      pwdInput.type = isPwd ? 'text' : 'password';
      toggleBtn.textContent = isPwd ? 'Hide' : 'Show';
    });

    document.querySelectorAll('.demo-account-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('email').value = btn.dataset.email;
        pwdInput.value = 'greenleaf';
        document.getElementById('loginForm').submit();
      });
    });
  </script>
</body>
</html>
