<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,600&family=JetBrains+Mono:wght@400;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <header class="landing-nav">
    <a href="/" class="brand-lockup">
      <span class="brand-mark">+</span>
      <span>Dispensa</span>
    </a>
    <nav class="landing-links">
      <a href="#counter">At the counter</a>
      <a href="#backoffice">Back office</a>
      <a href="#roles">Roles</a>
    </nav>
    <a href="/login" class="btn btn-dark btn-sm">Sign in &rarr;</a>
  </header>

  <main>
    <section class="landing-hero">
      <div>
        <div class="hero-pill">
          <span class="hero-pill-dot"></span>
          <span>Pharmacy management for one admin and a whole counter team</span>
        </div>
        <h1 class="hero-title">Every tablet,<br>every bill,<br><em>one ledger.</em></h1>
        <p class="hero-sub">Dispensa answers &ldquo;do we have it?&rdquo; in under a second, prints the customer&rsquo;s bill, and taps the owner on the shoulder the moment a shelf drops below 5 units.</p>
        <div class="hero-ctas">
          <a href="/login" class="btn btn-primary">Open the demo pharmacy &rarr;</a>
          <a href="#counter" class="btn btn-outline">See how it works</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-val">&lt; 5</div>
            <div class="hero-stat-lbl">units triggers an alert</div>
          </div>
          <div>
            <div class="hero-stat-val">3</div>
            <div class="hero-stat-lbl">sales views: day, week, month</div>
          </div>
          <div>
            <div class="hero-stat-val">2</div>
            <div class="hero-stat-lbl">roles: admin and staff</div>
          </div>
        </div>
      </div>

      <div class="hero-stage">
        <div class="hero-mockup-card">
          <div class="mockup-search">
            <span style="color:var(--ink-muted);">&#128269;</span>
            <input type="text" id="heroSearchInput" value="azith" aria-label="Demo medicine search">
          </div>
          <div id="heroSearchResults">
            <div class="mockup-row">
              <div>
                <strong>Zimax</strong> <span class="badge-rx">Rx</span>
                <div style="font-size:11.5px;color:var(--ink-muted);">Azithromycin 500 mg</div>
              </div>
              <span class="badge badge-terracotta">4 left</span>
            </div>
            <div class="mockup-row">
              <div>
                <strong>Azithrocin</strong> <span class="badge-rx">Rx</span>
                <div style="font-size:11.5px;color:var(--ink-muted);">Azithromycin 250 mg</div>
              </div>
              <span class="badge badge-green">42 in stock</span>
            </div>
          </div>
        </div>

        <div class="floating-receipt-card">
          <div style="text-align:center;font-weight:700;font-size:12px;">Greenleaf Pharmacy</div>
          <div style="text-align:center;font-size:10px;color:var(--ink-muted);margin-bottom:8px;">INV-02841 &middot; Tom&aacute;s O.</div>
          <div class="thermal-divider"></div>
          <div style="display:flex;justify-content:space-between;margin-bottom:3px;"><span>Zimax 500 mg &times;6</span><span>$11.10</span></div>
          <div style="display:flex;justify-content:space-between;margin-bottom:3px;"><span>Napa 500 mg &times;20</span><span>$2.40</span></div>
          <div style="display:flex;justify-content:space-between;"><span>Fexo 120 mg &times;14</span><span>$8.82</span></div>
          <div class="thermal-divider"></div>
          <div style="display:flex;justify-content:space-between;font-weight:700;font-size:12.5px;"><span>Total</span><span>$22.32</span></div>
          <div style="text-align:right;font-size:10px;color:var(--ink-muted);margin-top:2px;">Card &middot; Paid in</div>
        </div>

        <div class="floating-toast-card">
          <span class="toast-icon-box">!</span>
          <div>
            <div style="font-weight:600;font-size:12.5px;">Zimax 500 mg is down to 4 units</div>
            <div style="font-size:11px;opacity:0.7;">Stock below 5 &middot; Shelf B1 &middot; Just now</div>
          </div>
        </div>
      </div>
    </section>

    <section id="counter" class="landing-section">
      <div class="counter-split">
        <div>
          <div class="eyebrow" style="color:var(--terracotta);">AT THE COUNTER</div>
          <h2 class="section-heading" style="margin-top:10px;">Customer asks. Staff types three letters. Done.</h2>
          <p style="color:var(--ink-secondary);max-width:400px;font-size:15px;">Search by brand, generic name or shelf. Availability, price, expiry and prescription status show up before the customer finishes the sentence.</p>
        </div>

        <div class="counter-steps-stack">
          <div class="counter-step-row">
            <span class="step-idx">01</span>
            <span class="step-ico">&#128269;</span>
            <div>
              <strong>Find the medicine</strong>
              <div style="font-size:13px;color:var(--ink-secondary);">Brand or generic, fuzzy-matched, with live stock and shelf location.</div>
            </div>
          </div>
          <div class="counter-step-row">
            <span class="step-idx">02</span>
            <span class="step-ico">&#128196;</span>
            <div>
              <strong>Build the bill</strong>
              <div style="font-size:13px;color:var(--ink-secondary);">Add quantities, apply a discount, pick cash, card or mobile pay.</div>
            </div>
          </div>
          <div class="counter-step-row">
            <span class="step-idx">03</span>
            <span class="step-ico">&#128424;</span>
            <div>
              <strong>Print the receipt</strong>
              <div style="font-size:13px;color:var(--ink-secondary);">A clean 80 mm receipt with invoice number, served-by and totals.</div>
            </div>
          </div>
          <div class="counter-step-row">
            <span class="step-idx">04</span>
            <span class="step-ico">&#128230;</span>
            <div>
              <strong>Stock updates itself</strong>
              <div style="font-size:13px;color:var(--ink-secondary);">If a sale takes a shelf under 5, the admin is alerted at once.</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="backoffice" class="landing-section">
      <div class="bento-top-row">
        <div class="bento-card featured-green">
          <div style="opacity:0.75;margin-bottom:14px;">&#8599;</div>
          <h3 class="serif" style="font-size:26px;margin:0 0 10px;font-weight:500;">Who sold what, today, this week, this month.</h3>
          <p style="margin:0;opacity:0.85;max-width:460px;">The admin dashboard breaks revenue down by staff member, by day and by medicine, with every bill one click away.</p>
        </div>
        <div class="bento-card featured-peach">
          <div style="color:var(--terracotta);margin-bottom:14px;">&#9888;</div>
          <h3 class="serif" style="font-size:22px;margin:0 0 10px;font-weight:500;">Low-stock alerts</h3>
          <p style="margin:0;color:var(--ink-secondary);">Below 5 units? It lands in the admin&rsquo;s bell with a one-tap restock.</p>
        </div>
      </div>

      <div class="bento-bottom-row">
        <div class="bento-card">
          <div style="color:var(--amber);margin-bottom:12px;">&#128337;</div>
          <h3 class="serif" style="font-size:20px;margin:0 0 8px;font-weight:500;">Expiry watch</h3>
          <p style="margin:0;color:var(--ink-secondary);">Batches nearing expiry are flagged so they move first, or go back to the supplier.</p>
        </div>
        <div class="bento-card">
          <div style="color:var(--green-800);margin-bottom:12px;">&#128101;</div>
          <h3 class="serif" style="font-size:20px;margin:0 0 8px;font-weight:500;">Staff accounts</h3>
          <p style="margin:0;color:var(--ink-secondary);">Add a new hire in seconds, suspend access without losing their sales history.</p>
        </div>
        <div class="bento-card">
          <div style="color:var(--green-800);margin-bottom:12px;">&#128737;</div>
          <h3 class="serif" style="font-size:20px;margin:0 0 8px;font-weight:500;">Rx flags</h3>
          <p style="margin:0;color:var(--ink-secondary);">Prescription-only medicines are marked at checkout so nothing slips over the counter.</p>
        </div>
      </div>
    </section>

    <section id="roles" class="landing-section">
      <div class="role-ledger-wrapper">
        <div class="role-ledger-col">
          <h3 class="serif" style="font-size:22px;margin:0 0 16px;">The admin</h3>
          <ul class="role-list role-list-terracotta">
            <li>Sales dashboard across all staff</li>
            <li>Add, restock and retire medicines</li>
            <li>Add or remove staff accounts</li>
            <li>Every bill, filterable and printable</li>
            <li>Profile and password settings</li>
          </ul>
        </div>
        <div class="role-ledger-col">
          <h3 class="serif" style="font-size:22px;margin:0 0 16px;">The counter staff</h3>
          <ul class="role-list role-list-green">
            <li>Instant medicine search with availability</li>
            <li>Checkout bills with discounts and receipts</li>
            <li>Daily, weekly and monthly sales</li>
            <li>Personal sales record</li>
            <li>Profile and password settings</li>
          </ul>
        </div>
      </div>
    </section>
  </main>

  <footer class="landing-footer">
    <div class="landing-footer-inner">
      <a href="/" class="brand-lockup">
        <span class="brand-mark">+</span>
        <span>Dispensa</span>
      </a>
      <div style="font-size:13px;color:var(--ink-muted);">Built for neighbourhood pharmacies &middot; &copy; <?= date('Y') ?></div>
    </div>
  </footer>

  <script>
    const heroInput = document.getElementById('heroSearchInput');
    const heroResults = document.getElementById('heroSearchResults');
    const demoItems = [
      { brand: 'Zimax', generic: 'Azithromycin 500 mg', stock: 4, rx: true },
      { brand: 'Azithrocin', generic: 'Azithromycin 250 mg', stock: 42, rx: true },
      { brand: 'Napa Extra', generic: 'Paracetamol 500 mg', stock: 140, rx: false },
      { brand: 'Alatrol', generic: 'Cetirizine 10 mg', stock: 1, rx: false },
    ];
    if (heroInput) {
      heroInput.addEventListener('input', () => {
        const q = heroInput.value.trim().toLowerCase();
        const matches = demoItems.filter(i => i.brand.toLowerCase().includes(q) || i.generic.toLowerCase().includes(q));
        heroResults.innerHTML = matches.slice(0, 2).map(m => `
          <div class="mockup-row">
            <div>
              <strong>${m.brand}</strong> ${m.rx ? '<span class="badge-rx">Rx</span>' : ''}
              <div style="font-size:11.5px;color:var(--ink-muted)">${m.generic}</div>
            </div>
            <span class="badge ${m.stock < 5 ? 'badge-terracotta' : 'badge-green'}">${m.stock < 5 ? m.stock + ' left' : m.stock + ' in stock'}</span>
          </div>
        `).join('') || '<div style="padding:12px;color:var(--ink-muted);">No matching demo items.</div>';
      });
    }
  </script>
</body>
</html>
