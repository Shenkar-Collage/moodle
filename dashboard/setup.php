<?php
/**
 * One-time setup helper.
 * Run this once in a browser or CLI to generate a password hash,
 * then DELETE this file or restrict access to it.
 *
 * CLI: php setup.php
 * Browser: https://yoursite.com/dashboard/setup.php?action=hash&pass=yourpassword
 */

// Block in production unless explicitly allowed
define('ALLOW_SETUP', true); // set to false after use

if (!ALLOW_SETUP) {
    http_response_code(403);
    die('Setup is disabled.');
}

header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['action'] ?? ($argv[1] ?? 'info');

if ($action === 'hash') {
    $pass = $_GET['pass'] ?? ($argv[2] ?? '');
    if (!$pass) {
        echo "Usage (browser): setup.php?action=hash&pass=yourpassword\n";
        echo "Usage (CLI):     php setup.php hash yourpassword\n";
        exit;
    }
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    echo "Password hash for config.php:\n\n";
    echo $hash . "\n\n";
    echo "Add this to config.php under admin > password_hash\n";

} else {
    echo "Shenkar Dashboard Setup Helper\n";
    echo "==============================\n\n";
    echo "1. Copy config.example.php to config.php\n";
    echo "2. Edit config.php with your DB credentials\n";
    echo "3. Generate admin password hash:\n";
    echo "   php setup.php hash YourAdminPassword\n";
    echo "   OR visit: setup.php?action=hash&pass=YourAdminPassword\n";
    echo "4. Paste the hash into config.php\n";
    echo "5. Delete setup.php\n\n";

    echo "PHP version: " . PHP_VERSION . "\n";
    echo "Extensions: ";
    echo (extension_loaded('pdo_pgsql') ? 'pdo_pgsql ✓' : 'pdo_pgsql ✗') . '  ';
    echo (extension_loaded('pdo_mysql') ? 'pdo_mysql ✓' : 'pdo_mysql ✗') . "\n";
}
