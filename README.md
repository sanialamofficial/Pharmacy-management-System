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
## interface :
## Home:
<img width="1500" height="823" alt="image" src="https://github.com/user-attachments/assets/038e40f8-cf04-4989-b8c9-eb238e6f9e4f" />
## Atthecounter:
<img width="1896" height="703" alt="image" src="https://github.com/user-attachments/assets/44f358be-2e72-4ea5-bf27-04d26abe33e2" />
## Back Office:
<img width="1575" height="673" alt="image" src="https://github.com/user-attachments/assets/10153e80-241c-4291-bb0c-baf0618b84b3" />
## Roles:
<img width="1888" height="697" alt="image" src="https://github.com/user-attachments/assets/f1279666-9795-4e5a-a941-d02f1ed6aee2" />
## Sign in:
<img width="1918" height="828" alt="image" src="https://github.com/user-attachments/assets/c9d46e30-62e1-4abf-bdd3-0da8ee2c3b4e" />
## Admin Panel :
<img width="1897" height="822" alt="image" src="https://github.com/user-attachments/assets/7a27c821-b3d4-4c69-8073-e523a25a21eb" />
<img width="1902" height="826" alt="image" src="https://github.com/user-attachments/assets/0acf3818-4f6c-4b15-9408-4888fe79d187" />
<img width="1897" height="832" alt="image" src="https://github.com/user-attachments/assets/53398858-5753-4650-938e-783d4485500c" />
<img width="1899" height="823" alt="image" src="https://github.com/user-attachments/assets/f84b2643-8688-4a92-89c6-1b4612cdf5ad" />
<img width="1902" height="823" alt="image" src="https://github.com/user-attachments/assets/e1a5bd36-9122-42e7-a9aa-3a0f2d083013" />
<img width="1902" height="822" alt="image" src="https://github.com/user-attachments/assets/9ed07091-27ac-4f34-a96b-549d7b6599e4" />
## Staff:
<img width="1894" height="822" alt="image" src="https://github.com/user-attachments/assets/b59161a3-09d6-4a97-a7b1-d4acd95bdba5" />
<img width="1897" height="829" alt="image" src="https://github.com/user-attachments/assets/926d08e6-ce65-40cf-a080-7c61f30935f9" />
<img width="1888" height="831" alt="image" src="https://github.com/user-attachments/assets/4f9de349-f6c6-4608-8c34-6839addad50e" />
<img width="1902" height="828" alt="image" src="https://github.com/user-attachments/assets/941fedf8-3182-4247-be99-46f5725c42cc" />
<img width="1897" height="829" alt="image" src="https://github.com/user-attachments/assets/9c2f8e0d-755d-4e64-b91e-067a8bf60b79" />





