<?php
// ── APP CONFIG (local XAMPP) ────────────────────────────────
return [
    'app_name' => 'SecureBank',
    'app_env'  => 'development',
    'app_debug'=> true,

    'security' => [
        'bcrypt_cost'        => 12,
        'max_login_attempts' => 5,
        'lockout_minutes'    => 15,
        'reset_token_ttl'    => 3600,
    ],
];
