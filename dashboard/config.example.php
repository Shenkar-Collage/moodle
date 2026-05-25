<?php
/**
 * Dashboard Configuration Example
 * Copy this file to config.php and fill in your settings.
 * NEVER commit config.php to git!
 *
 * Generate admin password hash via CLI:
 *   php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT) . PHP_EOL;"
 */
return [
    'db' => [
        'type'     => 'pgsql',   // 'pgsql' or 'mysqli'
        'host'     => 'localhost',
        'port'     => '5432',
        'dbname'   => 'moodle',
        'user'     => 'moodle',
        'password' => '',
        'prefix'   => 'mdl_',
    ],
    'admin' => [
        'username'      => 'admin',
        // Replace with: php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
        'password_hash' => '$2y$10$REPLACEME',
        'name'          => 'מנהל מערכת',
    ],
    'app' => [
        'title'        => 'דשבורד ראשי חוג',
        'institution'  => 'שנקר - מכללה להנדסה ועיצוב',
        'session_name' => 'shenkar_dashboard',
        'base_url'     => '/dashboard',  // URL path to this folder
    ],
];
