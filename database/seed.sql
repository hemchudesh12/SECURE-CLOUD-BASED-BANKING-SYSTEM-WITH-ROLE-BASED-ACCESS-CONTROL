-- ============================================================
-- SEED DATA — SecureBank v2
-- Credentials: Hem/Hem@2806 | teller01/Teller@123 | Mani/Mani@123 | jane_doe/Customer@123
-- ============================================================
USE banking_db;

-- ── ROLES ──────────────────────────────────────────────────
INSERT IGNORE INTO roles (id, name, description) VALUES
(1, 'administrator', 'Full system access'),
(2, 'teller',        'Bank staff operations'),
(3, 'customer',      'Account holder self-service');

-- ── PERMISSIONS ────────────────────────────────────────────
INSERT IGNORE INTO permissions (action_key, module, description) VALUES
('auth.login',                'auth',     'Can log in'),
('customer.dashboard',        'customer', 'View own dashboard'),
('customer.transfer',         'customer', 'Initiate fund transfers'),
('customer.history',          'customer', 'View own transaction history'),
('customer.profile',          'customer', 'Update own profile'),
('customer.beneficiaries',    'customer', 'Manage saved beneficiaries'),
('customer.scheduled',        'customer', 'Manage scheduled payments'),
('customer.statement',        'customer', 'Download account statements'),
('customer.analytics',        'customer', 'View spending analytics'),
('customer.support',          'customer', 'Raise support tickets'),
('teller.dashboard',          'teller',   'View teller dashboard'),
('teller.deposit',            'teller',   'Process deposits'),
('teller.withdrawal',         'teller',   'Process withdrawals'),
('teller.account_search',     'teller',   'Search customer accounts'),
('teller.approve',            'teller',   'Approve high-value transactions'),
('teller.fraud_alerts',       'teller',   'View fraud alerts'),
('teller.customer_profile',   'teller',   'View full customer profile'),
('teller.support',            'teller',   'Respond to support tickets'),
('admin.dashboard',           'admin',    'View admin dashboard'),
('admin.users',               'admin',    'Manage users'),
('admin.roles',               'admin',    'Manage roles and permissions'),
('admin.audit_log',           'admin',    'View audit logs'),
('admin.live_monitor',        'admin',    'Real-time transaction monitor'),
('admin.fraud_dashboard',     'admin',    'Review fraud flags'),
('admin.system_health',       'admin',    'System health dashboard'),
('admin.reports',             'admin',    'Generate reports'),
('admin.support_tickets',     'admin',    'Manage all support tickets'),
('admin.freeze_account',      'admin',    'Freeze / unfreeze accounts'),
('admin.scheduled_payments',  'admin',    'View all scheduled payments'),
('admin.reverse_transaction', 'admin',    'Reverse a completed transaction');

-- ── CUSTOMER PERMISSIONS ───────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE action_key IN (
    'auth.login','customer.dashboard','customer.transfer','customer.history',
    'customer.profile','customer.beneficiaries','customer.scheduled',
    'customer.statement','customer.analytics','customer.support'
);

-- ── TELLER PERMISSIONS ─────────────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE action_key IN (
    'auth.login','teller.dashboard','teller.deposit','teller.withdrawal',
    'teller.account_search','teller.approve','teller.fraud_alerts',
    'teller.customer_profile','teller.support','customer.history','customer.statement'
);

-- ── ADMIN GETS ALL PERMISSIONS ─────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- ── DEMO USERS ─────────────────────────────────────────────
-- Hem@2806     → administrator
-- Teller@123   → teller
-- Mani@123     → customer
-- Customer@123 → customer
INSERT IGNORE INTO users (id, username, full_name, email, phone, password_hash, role_id, is_active, email_verified) VALUES
(1, 'Hem',      'Hem (Administrator)', 'hem@bank.local',     '9000000001',
 '$2y$12$zDYt2gSCw3o8Bm1kCGEXNOupF87St8qbFcNDuMFxpXrvnN8sGFGmO', 1, 1, 1),
(2, 'teller01', 'Priya Sharma',        'teller@bank.local',  '9000000002',
 '$2y$12$pqydmjZL1cydp8G0RTay8.CWewr5vfe0nz6HP3M1ssqlMV9Q.zITq',  2, 1, 1),
(3, 'Mani',     'Mani (Customer)',     'mani@example.local', '9000000003',
 '$2y$12$TRT1e074.s1CmvNBjlL1z.TREZd/zXwMOuzIWX7Xt6Vm3oTWu9Pxa', 3, 1, 1),
(4, 'jane_doe', 'Jane Doe',            'jane@example.local', '9000000004',
 '$2y$12$lExNvJMD74tNmD3dPlWthOcxwF/8l8ec2DfjKuUhbudVz4toBQ/Zi',  3, 1, 1);

-- ── ACCOUNTS ───────────────────────────────────────────────
INSERT IGNORE INTO accounts (user_id, account_number, account_type, balance, interest_rate) VALUES
(3, 'ACC-20240001', 'savings',  50000.00, 3.50),
(4, 'ACC-20240002', 'checking', 25000.00, 2.00);

-- ── TRANSACTION LIMITS ─────────────────────────────────────
INSERT IGNORE INTO transaction_limits (role_id, daily_limit, single_limit, requires_approval_above, max_daily_count) VALUES
(3, 50000.00,   10000.00,  5000.00,   10),
(2, 500000.00,  100000.00, 50000.00,  50),
(1, 999999.99,  999999.99, 999999.99, 999);

-- ── SAMPLE BENEFICIARY ─────────────────────────────────────
INSERT IGNORE INTO beneficiaries (user_id, nickname, account_number, bank_name) VALUES
(3, 'Jane Doe', 'ACC-20240002', 'SecureBank');

-- ── SAMPLE TRANSACTIONS (realistic history) ────────────────
INSERT IGNORE INTO transactions (reference_number, from_account_id, to_account_id, amount, type, status, initiated_by, description, created_at) VALUES
('TXN-20240101-AABBCC', 1, 2, 5000.00,  'transfer',   'completed', 3, 'Rent payment',        '2024-01-01 10:00:00'),
('TXN-20240102-DDEEFF', NULL, 1, 10000.00,'deposit',   'completed', 2, 'Cash deposit',        '2024-01-02 11:00:00'),
('TXN-20240103-GGHHII', 1, NULL, 2000.00,'withdrawal', 'completed', 3, 'ATM withdrawal',      '2024-01-03 09:30:00'),
('TXN-20240104-JJKKLL', 1, 2, 3000.00,  'transfer',   'completed', 3, 'Grocery payment',     '2024-01-04 14:00:00'),
('TXN-20240105-MMNNOO', 1, 2, 8000.00,  'transfer',   'completed', 3, 'Utility bills',       '2024-01-05 16:00:00'),
('TXN-20240106-PPQQRR', NULL, 1, 20000.00,'deposit',   'completed', 2, 'Salary deposit',      '2024-01-06 08:00:00'),
('TXN-20240107-SSTTUU', 1, 2, 1500.00,  'transfer',   'completed', 3, 'Food & dining',       '2024-01-07 12:00:00'),
('TXN-20240108-VVWWXX', 1, 2, 4500.00,  'transfer',   'completed', 3, 'Shopping',            '2024-01-08 15:00:00'),
('TXN-20240110-YYZZAA', NULL, 1, 5000.00,'deposit',    'completed', 2, 'Bonus credit',        '2024-01-10 10:00:00'),
('TXN-20240115-BBCCDD', 1, 2, 6000.00,  'transfer',   'completed', 3, 'Insurance premium',   '2024-01-15 09:00:00');

-- ── SAMPLE NOTIFICATIONS ───────────────────────────────────
INSERT IGNORE INTO notifications (user_id, type, title, message, is_read, created_at) VALUES
(3, 'credit', 'Money Received',   '₹10,000.00 credited to your account ACC-20240001', 0, NOW()),
(3, 'debit',  'Transfer Sent',    '₹5,000.00 debited from your account ACC-20240001', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'info',   'Welcome!',         'Welcome to SecureBank. Your account is active.',   1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(4, 'credit', 'Salary Credited',  '₹20,000.00 credited to your account ACC-20240002',0, NOW()),
(1, 'info',   'System Ready',     'All systems operational. WebSocket server active.', 1, NOW());

-- ── SAMPLE SUPPORT TICKET ──────────────────────────────────
INSERT IGNORE INTO support_tickets (user_id, subject, message, status, priority) VALUES
(3, 'Unable to make transfer', 'I tried to transfer ₹10,000 but it keeps failing. Please help.', 'open',        'high'),
(4, 'Account statement query', 'Please send me my last 3 months statement.',                       'in_progress', 'medium');

-- ── SAMPLE FRAUD FLAG ──────────────────────────────────────
INSERT IGNORE INTO fraud_flags (transaction_id, rule_triggered, risk_score, review_status) VALUES
(5, 'large_amount,high_frequency', 65, 'pending');
