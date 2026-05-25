<?php
/**
 * Dashboard Configuration Example
 * Copy this file to config.php and fill in your settings.
 * NEVER commit config.php to git!
 *
 * Generate admin password hash via CLI:
 *   php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT) . PHP_EOL;"
 *
 * Find your Moodle DB details in /var/www/moodle/config.php:
 *   $CFG->dbtype, $CFG->dbhost, $CFG->dbname, $CFG->dbuser, $CFG->dbpass, $CFG->prefix
 */
return [
    'db' => [
        // Match $CFG->dbtype from Moodle's config.php
        // Accepted values: 'mariadb', 'mysqli', 'pgsql'
        'type'     => 'mariadb',
        'host'     => 'localhost',       // $CFG->dbhost
        'port'     => '3306',            // 3306 for MariaDB/MySQL, 5432 for PostgreSQL
        'dbname'   => 'moodle',          // $CFG->dbname
        'user'     => 'moodle',          // $CFG->dbuser
        'password' => '',                // $CFG->dbpass
        'prefix'   => 'mdl_',           // $CFG->prefix  (usually 'mdl_')
    ],
    'admin' => [
        'username'      => 'admin',
        // Generate with: php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
        'password_hash' => '$2y$10$REPLACEME',
        'name'          => 'מנהל מערכת',
    ],
    'app' => [
        'title'        => 'דשבורד ראשי חוג',
        'institution'  => 'שנקר - מכללה להנדסה ועיצוב',
        'session_name' => 'shenkar_dashboard',
        'base_url'     => '/dashboard',          // URL path to this folder (no trailing slash)
        'moodle_url'   => 'https://yoursite.ac.il', // Base URL of your Moodle (for message links)
    ],
];
