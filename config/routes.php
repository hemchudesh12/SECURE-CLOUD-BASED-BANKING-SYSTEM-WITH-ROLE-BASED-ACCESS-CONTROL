<?php
// ── ROUTE DEFINITIONS ──────────────────────────────────────
// $router is already instantiated in public/index.php

// ── Root redirect ──────────────────────────────────────────
$router->add('GET',  '/',                  'AuthController@showLogin');

// ── Auth routes (no permission check) ─────────────────────
$router->add('GET',  '/login',                'AuthController@showLogin');
$router->add('POST', '/login',                'AuthController@login');
$router->add('GET',  '/logout',               'AuthController@handleLogout');
$router->add('GET',  '/register',             'AuthController@showRegister');
$router->add('POST', '/register',             'AuthController@register');
$router->add('GET',  '/reset-password',       'AuthController@showResetRequest');
$router->add('POST', '/reset-password',       'AuthController@handleResetRequest');
$router->add('GET',  '/reset-password/:token','AuthController@showResetForm');
$router->add('POST', '/reset-password/:token','AuthController@handleResetForm');

// ── Notification routes ────────────────────────────────────
$router->add('POST', '/notifications/mark-read', 'NotificationController@markRead',   'auth.login');
$router->add('GET',  '/notifications/list',       'NotificationController@list',       'auth.login');
$router->add('GET',  '/notifications/count',      'NotificationController@count',      'auth.login');

// ── Support ticket (shared) ────────────────────────────────
$router->add('GET',  '/support/ticket/:id',       'SupportController@viewTicket',      'auth.login');
$router->add('POST', '/support/ticket/:id/reply', 'SupportController@reply',           'auth.login');

// ── Customer routes ────────────────────────────────────────
$router->add('GET',  '/customer/dashboard',        'CustomerController@dashboard',         'customer.dashboard');
$router->add('GET',  '/customer/transfer',         'CustomerController@showTransfer',       'customer.transfer');
$router->add('POST', '/customer/transfer',         'CustomerController@processTransfer',    'customer.transfer');
$router->add('GET',  '/customer/history',          'CustomerController@history',            'customer.history');
$router->add('GET',  '/customer/export',           'CustomerController@exportStatement',    'customer.history');
$router->add('GET',  '/customer/profile',          'CustomerController@showProfile',        'customer.profile');
$router->add('POST', '/customer/profile',          'CustomerController@updateProfile',      'customer.profile');
$router->add('POST', '/customer/profile/password', 'CustomerController@changePassword',     'customer.profile');
$router->add('GET',  '/customer/beneficiaries',    'BeneficiaryController@index',           'customer.beneficiaries');
$router->add('POST', '/customer/beneficiaries/add','BeneficiaryController@add',             'customer.beneficiaries');
$router->add('POST', '/customer/beneficiaries/remove/:id','BeneficiaryController@remove',   'customer.beneficiaries');
$router->add('GET',  '/customer/beneficiaries/lookup','BeneficiaryController@lookup',       'customer.beneficiaries');
$router->add('GET',  '/customer/analytics',        'CustomerController@analytics',          'customer.analytics');
$router->add('GET',  '/customer/analytics/data',   'CustomerController@analyticsData',      'customer.analytics');
$router->add('GET',  '/customer/statement',        'StatementController@showStatement',     'customer.statement');
$router->add('GET',  '/customer/statement/pdf',    'StatementController@downloadPDF',       'customer.statement');
$router->add('GET',  '/customer/statement/csv',    'StatementController@downloadCSV',       'customer.statement');
$router->add('GET',  '/customer/scheduled',        'CustomerController@scheduledPayments',  'customer.scheduled');
$router->add('POST', '/customer/scheduled/add',    'CustomerController@addScheduled',       'customer.scheduled');
$router->add('POST', '/customer/scheduled/cancel/:id','CustomerController@cancelScheduled', 'customer.scheduled');
$router->add('GET',  '/customer/support',          'SupportController@customerIndex',       'customer.support');
$router->add('POST', '/customer/support/create',   'SupportController@create',              'customer.support');
// ── Customer Loan routes ───────────────────────────────────
$router->add('GET',  '/customer/loans',        'LoanController@customerIndex',  'customer.dashboard');
$router->add('POST', '/customer/loans/apply',  'LoanController@apply',          'customer.dashboard');

// ── Admin routes ───────────────────────────────────────────
$router->add('GET',  '/admin/dashboard',              'AdminController@dashboard',          'admin.dashboard');
$router->add('GET',  '/admin/users',                  'AdminController@listUsers',           'admin.users');
$router->add('GET',  '/admin/users/create',           'AdminController@showCreateUser',      'admin.users');
$router->add('POST', '/admin/users/create',           'AdminController@createUser',          'admin.users');
$router->add('GET',  '/admin/users/:id/edit',         'AdminController@editUser',            'admin.users');
$router->add('POST', '/admin/users/:id/edit',         'AdminController@updateUser',          'admin.users');
$router->add('POST', '/admin/users/:id/toggle',       'AdminController@toggleUser',          'admin.users');
$router->add('POST', '/admin/users/:id/delete',       'AdminController@deleteUser',          'admin.users');
$router->add('POST', '/admin/users/:id/reset-password','AdminController@resetUserPassword',  'admin.users');

$router->add('POST', '/admin/accounts/:id/freeze',    'AdminController@freezeAccount',       'admin.freeze_account');
$router->add('POST', '/admin/accounts/:id/unfreeze',  'AdminController@unfreezeAccount',     'admin.freeze_account');
$router->add('POST', '/admin/transactions/:id/reverse','AdminController@reverseTransaction', 'admin.reverse_transaction');
$router->add('GET',  '/admin/roles',                  'AdminController@listRoles',           'admin.roles');
$router->add('POST', '/admin/roles',                  'AdminController@saveRole',            'admin.roles');
$router->add('GET',  '/admin/roles/:id/permissions',  'AdminController@editPermissions',     'admin.roles');
$router->add('POST', '/admin/roles/:id/permissions',  'AdminController@savePermissions',     'admin.roles');
$router->add('GET',  '/admin/audit',                  'AdminController@auditLog',            'admin.audit_log');
$router->add('GET',  '/admin/audit/export',           'AdminController@exportAudit',         'admin.audit_log');
$router->add('GET',  '/admin/integrity',              'AdminController@integrityCheck',      'admin.audit_log');
$router->add('GET',  '/admin/system',                 'AdminController@systemHealth',        'admin.system_health');
$router->add('GET',  '/admin/reports',                'AdminController@reports',             'admin.reports');
$router->add('GET',  '/admin/fraud',                  'AdminController@fraudDashboard',      'admin.fraud_dashboard');
$router->add('POST', '/admin/fraud/:id/review',       'AdminController@reviewFraud',         'admin.fraud_dashboard');
$router->add('GET',  '/admin/live-monitor',           'AdminController@liveMonitor',         'admin.live_monitor');
$router->add('GET',  '/admin/support',                'SupportController@adminIndex',        'admin.support_tickets');
$router->add('POST', '/admin/support/:id/status',     'SupportController@updateStatus',      'admin.support_tickets');
$router->add('GET',  '/admin/scheduled',              'AdminController@scheduledPayments',   'admin.scheduled_payments');
$router->add('POST', '/admin/broadcast',              'AdminController@broadcastAlert',      'admin.dashboard');
$router->add('GET',  '/admin/statement/:id',          'StatementController@downloadForAccount','admin.reports');
// ── Admin Loan Management ──────────────────────────────────
$router->add('GET',  '/admin/loans',              'LoanController@adminIndex',  'admin.dashboard');
$router->add('POST', '/admin/loans/:id/approve',  'LoanController@approve',    'admin.dashboard');
$router->add('POST', '/admin/loans/:id/reject',   'LoanController@reject',     'admin.dashboard');

// ── AJAX ──────────────────────────────────────────────────
$router->add('GET',  '/api/ws-poll',  'AdminController@wsPoll',       'auth.login');
$router->add('GET',  '/api/balance',  'CustomerController@apiBalance', 'customer.dashboard');
