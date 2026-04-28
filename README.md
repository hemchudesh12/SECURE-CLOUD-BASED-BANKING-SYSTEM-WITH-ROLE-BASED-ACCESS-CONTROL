<div align="center">
  <h1>🏛️ Secure Cloud-Based Banking System</h1>
  <p><i>A robust, modern, and highly secure cloud banking platform featuring Role-Based Access Control (RBAC).</i></p>
</div>

---

## 🌟 Overview

Welcome to the **Secure Cloud-Based Banking System**! This application is designed to simulate a real-world, production-ready banking environment. Built with PHP and MySQL, it features a stunning **Navy and Gold** premium interface, high-end security protocols, and seamless transaction processing. 

Whether you're an Administrator overseeing the entire platform or a Customer managing your personal finances, this system provides a smooth, intuitive, and highly secure experience.

---

## ✨ Key Features

### 🛡️ Uncompromised Security & Architecture
- **Role-Based Access Control (RBAC):** Strict separation of duties. Administrators have full system control, while Customers have access only to their individual accounts.
- **End-to-End Encryption:** User passwords and sensitive data are protected with industry-standard hashing algorithms.
- **Atomic Transactions:** Powered by robust MySQL transactions to ensure that money is never lost or duplicated during transfers.
- **CSRF & XSS Protection:** Built-in middleware protects against Cross-Site Request Forgery and Cross-Site Scripting attacks.

### 🏦 Customer Portal
- **Dashboard:** A beautiful, responsive interface displaying live balances, quick actions, and recent activity.
- **Fund Transfers:** Send money instantly to any account within the network using an intuitive transfer wizard.
- **Beneficiary Management:** Save frequently used accounts for rapid, one-click transfers.
- **Support Center:** Open tickets and communicate directly with bank administrators for assistance.

### 📊 Administrative Dashboard
- **System Overview:** Get a bird's-eye view of total users, active accounts, daily transaction volumes, and overall system health.
- **Fraud Monitoring:** An AI-powered simulation that automatically flags suspicious or abnormally large transactions for manual administrative review.
- **User Management:** Complete control over account creation, role assignments, and account suspensions.
- **Comprehensive Audit Logs:** Every critical action taken within the system is permanently logged for security and compliance audits.

---

## 💻 Tech Stack

- **Backend:** PHP 8.2+ (Custom MVC Architecture)
- **Database:** MySQL / MariaDB (Relational data modeling, ACID compliance)
- **Frontend:** HTML5, CSS3 (Vanilla, Custom Design System), Vanilla JavaScript
- **Visuals:** Chart.js for data visualization, modern dynamic micro-animations.

---

## 🚀 Getting Started

Follow these simple steps to get the banking system running on your local machine.

### Prerequisites
- A local web server stack (e.g., [XAMPP](https://www.apachefriends.org/), MAMP, or WAMP)
- PHP 8.2 or higher
- MySQL database server

### Installation Steps

1. **Clone the Repository**  
   Open your terminal and clone this repository into your web server's root directory (e.g., `htdocs` for XAMPP):
   ```bash
   git clone https://github.com/hemchudesh12/SECURE-CLOUD-BASED-BANKING-SYSTEM-WITH-ROLE-BASED-ACCESS-CONTROL.git
   ```

2. **Database Setup**  
   - Open phpMyAdmin (or your preferred database management tool).
   - Create a new, empty database (e.g., `banking_system`).
   - Import the provided SQL schema file (located in the repository) into your new database to generate the necessary tables.

3. **Environment Configuration**  
   - Locate the configuration file (typically in `src/Database.php` or a `.env` file if available).
   - Update the database credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) to match your local MySQL setup.

4. **Launch the Application**  
   - Start your Apache and MySQL servers via your control panel.
   - Open your favorite web browser and navigate to the application:
     ```text
     http://localhost/SECURE-CLOUD-BASED-BANKING-SYSTEM-WITH-ROLE-BASED-ACCESS-CONTROL/public
     ```

---

## 🎨 Design Philosophy

This application steps away from generic templates, utilizing a fully custom **Navy Blue (`#0D1B2A`) and Gold (`#C9972B`)** design system. The interface is crafted to feel premium, trustworthy, and modern. 

- **Glassmorphism & Depth:** Soft shadows and layered cards provide visual hierarchy.
- **Micro-interactions:** Smooth hover states, dynamic charting, and seamless form validations make the app feel alive and responsive.
- **Responsive by Nature:** Flawless experience across desktops, tablets, and mobile devices.

---

## 🤝 Contributor

- **[hemchudesh12](https://github.com/hemchudesh12)** - *Lead Developer*

---

<div align="center">
  <p><i>Built with security and user experience in mind.</i></p>
</div>
