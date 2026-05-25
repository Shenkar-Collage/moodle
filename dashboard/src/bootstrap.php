<?php
define('DASHBOARD_ROOT', __DIR__ . '/..');

require_once DASHBOARD_ROOT . '/src/helpers.php';
require_once DASHBOARD_ROOT . '/src/Database.php';
require_once DASHBOARD_ROOT . '/src/Auth.php';
require_once DASHBOARD_ROOT . '/src/Settings.php';
require_once DASHBOARD_ROOT . '/src/MoodleData.php';

$cfg = require DASHBOARD_ROOT . '/config.php';
define('BASE_URL',    rtrim($cfg['app']['base_url']    ?? '/dashboard', '/'));
define('APP_TITLE',   $cfg['app']['title']             ?? 'דשבורד ראשי חוג');
define('INSTITUTION', $cfg['app']['institution']       ?? 'שנקר');
define('MOODLE_URL',  rtrim($cfg['app']['moodle_url']  ?? '', '/'));

Auth::init();
