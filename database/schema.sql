-- Dispensa Pharmacy Management System - SQLite Schema

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('admin', 'staff')),
    job_title TEXT NOT NULL,
    shift TEXT NOT NULL DEFAULT 'Morning (8a-4p)',
    phone TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active', 'suspended')),
    joined_at TEXT NOT NULL DEFAULT (date('now')),
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS medicines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    brand_name TEXT NOT NULL,
    generic_name TEXT NOT NULL,
    strength TEXT NOT NULL,
    form TEXT NOT NULL,
    category TEXT NOT NULL,
    manufacturer TEXT NOT NULL,
    shelf_location TEXT NOT NULL,
    unit_price REAL NOT NULL,
    stock_qty INTEGER NOT NULL DEFAULT 0,
    batch_no TEXT NOT NULL,
    expiry_date TEXT NOT NULL,
    rx_required INTEGER NOT NULL DEFAULT 0 CHECK(rx_required IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS sales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_no TEXT NOT NULL UNIQUE,
    user_id INTEGER NOT NULL,
    customer_name TEXT DEFAULT '',
    customer_phone TEXT DEFAULT '',
    payment_method TEXT NOT NULL CHECK(payment_method IN ('Cash', 'Card', 'Mobile')),
    discount_pct INTEGER NOT NULL DEFAULT 0,
    subtotal REAL NOT NULL,
    discount_amount REAL NOT NULL DEFAULT 0,
    total_amount REAL NOT NULL,
    rx_checked INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS sale_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sale_id INTEGER NOT NULL,
    medicine_id INTEGER NOT NULL,
    medicine_name TEXT NOT NULL,
    qty INTEGER NOT NULL,
    unit_price REAL NOT NULL,
    line_total REAL NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS notification_reads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    alert_key TEXT NOT NULL,
    read_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, alert_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
