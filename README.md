# Dispensa — Pharmacy Management System (PHP MVC)

A complete **PHP 8+ MVC** pharmacy management system with a **vanilla HTML, CSS, and JavaScript** frontend and **SQLite (PDO)** backend, modeled after Dispensa (`Greenleaf Community Pharmacy`).

## Quick Start (Zero Config)

Requires **PHP 8.0+** with `pdo_sqlite` enabled (standard in PHP, XAMPP, Laragon, Homebrew, and Ubuntu/Debian).

```bash
cd dispensa-mvc
php -S localhost:8000 -t public
```

Open http://localhost:8000 in your browser.
On first request, `app/Core/Database.php` automatically creates `database/dispensa.sqlite`, applies `database/schema.sql`, and seeds all demo users, 32 medicines, and 30 days of sales history.

## Demo Accounts (Password: `greenleaf`)

| Name | Email | Role |
|---|---|---|
| Rehana Kabir | `rehana@greenleaf.rx` | Admin |
| Tomás Okafor | `tomas@greenleaf.rx` | Staff |
| Priya Venkataraman | `priya@greenleaf.rx` | Staff |
| Elin Marsh | `elin@greenleaf.rx` | Staff |

## MVC Directory Structure

```text
dispensa-mvc/
├── app/
│   ├── Core/           # Router, Controller, Model, Database (PDO SQLite)
│   ├── Controllers/    # HomeController, AuthController, AdminController, StaffController
│   ├── Models/         # User, Medicine, Sale, Notification
│   └── Views/          # layouts/app.php, home/, auth/, admin/, staff/, shared/
├── config/
│   └── config.php      # Pharmacy metadata, thresholds, DB path
├── database/
│   └── schema.sql      # SQLite schema (users, medicines, sales, sale_items, notification_reads)
└── public/
    ├── index.php       # Front controller & route definitions
    ├── .htaccess       # Apache mod_rewrite rules
    └── assets/
        ├── css/styles.css
        └── js/app.js
```

## Key Features

- **Public Landing & Split Login:** Interactive search preview, 80mm receipt preview, and one-click demo accounts.
- **Admin Console:** Today/Week/Month KPIs, 7-day revenue chart, low-stock & expiry bell popover with one-click restock, staff performance table, full inventory CRUD, staff management (add, suspend/reactivate, reset password, delete), sales ledger with CSV export, and Counter POS checkout.
- **Staff Counter:** Scoped personal dashboard (`My dashboard`), hourly sales chart with team rank, `New bill` POS with `/` search shortcut, Rx verification guardrail, and `My sales` with 80mm thermal receipt reprint.
