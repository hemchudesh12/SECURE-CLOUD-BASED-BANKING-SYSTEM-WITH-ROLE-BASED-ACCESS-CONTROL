-- ============================================================
-- SECURE BANKING SYSTEM — COMPLETE DATABASE SCHEMA v2
-- MySQL 8.x | utf8mb4 | XAMPP
-- ============================================================

CREATE DATABASE IF NOT EXISTS banking_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE banking_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ── ROLES ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── PERMISSIONS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_key  VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    module      VARCHAR(50)
) ENGINE=InnoDB;

-- ── ROLE → PERMISSION MAPPING ──────────────────────────────
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── USERS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(100) NOT NULL UNIQUE,
    full_name       VARCHAR(150) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    phone           VARCHAR(20),
    password_hash   VARCHAR(255) NOT NULL,
    role_id         INT UNSIGNED NOT NULL,
    is_active       TINYINT(1)   DEFAULT 1,
    email_verified  TINYINT(1)   DEFAULT 1,
    two_fa_enabled  TINYINT(1)   DEFAULT 0,
    two_fa_secret   VARCHAR(32),
    login_failures  INT          DEFAULT 0,
    locked_until    DATETIME,
    last_login_at   DATETIME,
    last_login_ip   VARCHAR(45),
    remember_token  VARCHAR(64),
    remember_expiry DATETIME,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ── PASSWORD RESET ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    used_at    DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── ACCOUNTS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS accounts (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED  NOT NULL UNIQUE,
    account_number VARCHAR(20)   NOT NULL UNIQUE,
    account_type   ENUM('savings','checking','current') DEFAULT 'savings',
    balance        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    currency       CHAR(3)       DEFAULT 'INR',
    is_active      TINYINT(1)    DEFAULT 1,
    is_frozen      TINYINT(1)    DEFAULT 0,
    freeze_reason  TEXT,
    interest_rate  DECIMAL(5,2)  DEFAULT 3.50,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ── TRANSACTIONS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(30)   NOT NULL UNIQUE,
    from_account_id  INT UNSIGNED  DEFAULT NULL,
    to_account_id    INT UNSIGNED  DEFAULT NULL,
    amount           DECIMAL(15,2) NOT NULL,
    currency         CHAR(3)       DEFAULT 'INR',
    converted_amount DECIMAL(15,2),
    type             ENUM('transfer','deposit','withdrawal','interest','fee','reversal') NOT NULL,
    status           ENUM('completed','pending','failed','rejected','reversed') NOT NULL DEFAULT 'pending',
    initiated_by     INT UNSIGNED  DEFAULT NULL,
    approved_by      INT UNSIGNED  DEFAULT NULL,
    description      TEXT,
    note             VARCHAR(255),
    ip_address       VARCHAR(45),
    device_info      TEXT,
    is_flagged       TINYINT(1)    DEFAULT 0,
    flag_reason      TEXT,
    created_at       DATETIME(3)   DEFAULT CURRENT_TIMESTAMP(3),
    FOREIGN KEY (from_account_id) REFERENCES accounts(id),
    FOREIGN KEY (to_account_id)   REFERENCES accounts(id),
    INDEX idx_from   (from_account_id),
    INDEX idx_to     (to_account_id),
    INDEX idx_status (status),
    INDEX idx_flagged (is_flagged)
) ENGINE=InnoDB;

-- ── TRANSACTION LIMITS ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS transaction_limits (
    id                       INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    role_id                  INT UNSIGNED  NOT NULL,
    daily_limit              DECIMAL(15,2) DEFAULT 100000.00,
    single_limit             DECIMAL(15,2) DEFAULT 50000.00,
    requires_approval_above  DECIMAL(15,2) DEFAULT 25000.00,
    max_daily_count          INT           DEFAULT 10,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ── BENEFICIARIES ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS beneficiaries (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    nickname       VARCHAR(100) NOT NULL,
    account_number VARCHAR(20)  NOT NULL,
    bank_name      VARCHAR(100) DEFAULT 'SecureBank',
    is_active      TINYINT(1)   DEFAULT 1,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_acc (user_id, account_number)
) ENGINE=InnoDB;

-- ── SCHEDULED / RECURRING PAYMENTS ────────────────────────
CREATE TABLE IF NOT EXISTS scheduled_payments (
    id               INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED  NOT NULL,
    from_account_id  INT UNSIGNED  NOT NULL,
    to_account_id    INT UNSIGNED  NOT NULL,
    amount           DECIMAL(15,2) NOT NULL,
    description      VARCHAR(255),
    frequency        ENUM('once','daily','weekly','monthly') NOT NULL,
    next_run_at      DATETIME      NOT NULL,
    last_run_at      DATETIME,
    end_date         DATE,
    status           ENUM('active','paused','completed','failed') DEFAULT 'active',
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)         REFERENCES users(id),
    FOREIGN KEY (from_account_id) REFERENCES accounts(id),
    FOREIGN KEY (to_account_id)   REFERENCES accounts(id)
) ENGINE=InnoDB;

-- ── NOTIFICATIONS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED  NOT NULL,
    type       VARCHAR(50)   NOT NULL,
    title      VARCHAR(150)  NOT NULL,
    message    TEXT          NOT NULL,
    data       JSON,
    is_read    TINYINT(1)    DEFAULT 0,
    created_at DATETIME(3)   DEFAULT CURRENT_TIMESTAMP(3),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB;

-- ── SUPPORT TICKETS ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS support_tickets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT         NOT NULL,
    status      ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    priority    ENUM('low','medium','high','urgent')           DEFAULT 'medium',
    assigned_to INT UNSIGNED,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS support_replies (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id  INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    message    TEXT         NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)
) ENGINE=InnoDB;

-- ── FRAUD FLAGS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fraud_flags (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    rule_triggered VARCHAR(100)    NOT NULL,
    risk_score     TINYINT UNSIGNED DEFAULT 0,
    reviewed_by    INT UNSIGNED,
    review_status  ENUM('pending','cleared','confirmed') DEFAULT 'pending',
    reviewed_at    DATETIME,
    notes          TEXT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (reviewed_by)    REFERENCES users(id)
) ENGINE=InnoDB;

-- ── RATE LIMITS (per IP) ───────────────────────────────────
CREATE TABLE IF NOT EXISTS rate_limits (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45)  NOT NULL,
    action       VARCHAR(50)  NOT NULL,
    hit_count    INT          DEFAULT 1,
    window_start DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip_address, action)
) ENGINE=InnoDB;

-- ── WEBSOCKET SESSIONS ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS ws_sessions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL UNIQUE,
    conn_id      VARCHAR(64)  NOT NULL,
    connected_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    last_ping    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── WS OUTBOX (broadcast queue) ───────────────────────────
CREATE TABLE IF NOT EXISTS ws_outbox (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    event      VARCHAR(80)  NOT NULL,
    data       JSON,
    sent       TINYINT(1)   DEFAULT 0,
    created_at DATETIME(3)  DEFAULT CURRENT_TIMESTAMP(3),
    INDEX idx_pending (user_id, sent)
) ENGINE=InnoDB;

-- ── AUDIT LOGS (append-only, hash-chained) ─────────────────
CREATE TABLE IF NOT EXISTS audit_logs (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED  DEFAULT NULL,
    username      VARCHAR(100)  DEFAULT NULL,
    session_id    VARCHAR(255)  DEFAULT NULL,
    action        VARCHAR(100)  NOT NULL,
    entity_type   VARCHAR(50)   DEFAULT NULL,
    entity_id     INT UNSIGNED  DEFAULT NULL,
    source_ip     VARCHAR(45)   DEFAULT NULL,
    user_agent    TEXT,
    outcome       ENUM('success','failure') NOT NULL,
    metadata      JSON          DEFAULT NULL,
    previous_hash VARCHAR(64)   DEFAULT NULL,
    entry_hash    VARCHAR(64)   DEFAULT NULL,
    created_at    DATETIME(3)   DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB;

-- ── INTEREST ACCRUAL LOG ───────────────────────────────────
CREATE TABLE IF NOT EXISTS interest_log (
    id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    account_id INT UNSIGNED  NOT NULL,
    amount     DECIMAL(15,2) NOT NULL,
    rate       DECIMAL(5,2)  NOT NULL,
    period     VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
