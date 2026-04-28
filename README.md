# SecureBank — Real-Time Banking System

A production-grade real-time banking system built with PHP 8.2, MySQL, and Ratchet WebSockets.

## 🔑 Demo Credentials

| Role          | Username   | Password     |
|---------------|------------|--------------|
| Administrator | `Hem`      | `Hem@2806`   |
| Teller        | `teller01` | `Teller@123` |
| Customer 1    | `Mani`     | `Mani@123`   |
| Customer 2    | `jane_doe` | `Customer@123` |

## 🚀 Quick Setup

1. **Start XAMPP** — Start Apache and MySQL from XAMPP Control Panel

2. **Import database:**
   ```
   Open phpMyAdmin → Import → database/schema.sql
   Then import → database/seed.sql
   ```

3. **Install Composer dependencies:**
   ```bash
   cd C:\xampp\htdocs\banking-system
   composer require cboden/ratchet mpdf/mpdf endroid/qr-code
   ```

4. **Visit:** `http://localhost/banking-system/public/`

5. **Optional — Start WebSocket server** (for real-time features):
   ```bash
   C:\xampp\php\php.exe bin/websocket-server.php
   ```
   > Note: Without the WebSocket server, the system automatically falls back to AJAX polling every 3 seconds.

6. **Optional — Schedule payments runner:**
   ```
   schtasks /create /sc minute /mo 1 /tn "BankScheduler" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\banking-system\bin\run-scheduler.php"
   ```

## 📁 Key Files

- `database/schema.sql` — Complete database schema
- `database/seed.sql` — Demo users and sample data
- `config/db.php` — Database connection settings
- `public/index.php` — Front controller
- `bin/run-scheduler.php` — Scheduled payments processor

## 🔧 Configuration

Edit `config/db.php` to change database credentials or settings.
