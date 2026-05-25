<?php

class Auth
{
    public static function init(): void
    {
        $cfg = require DASHBOARD_ROOT . '/config.php';
        $name = $cfg['app']['session_name'] ?? 'shenkar_dashboard';
        if (session_status() === PHP_SESSION_NONE) {
            session_name($name);
            session_start();
        }
    }

    public static function login(string $username, string $password): bool
    {
        $cfg = require DASHBOARD_ROOT . '/config.php';

        // Check admin credentials
        if (
            $username === $cfg['admin']['username'] &&
            password_verify($password, $cfg['admin']['password_hash'])
        ) {
            self::setSession([
                'username'    => $username,
                'role'        => 'admin',
                'name'        => $cfg['admin']['name'] ?? 'מנהל מערכת',
                'departments' => [],
            ]);
            return true;
        }

        // Check department heads
        foreach (self::loadUsers() as $user) {
            if (
                $user['active'] &&
                $user['username'] === $username &&
                password_verify($password, $user['password_hash'])
            ) {
                self::setSession([
                    'username'    => $username,
                    'role'        => 'head',
                    'name'        => $user['name'],
                    'departments' => $user['department_codes'],
                ]);
                return true;
            }
        }

        return false;
    }

    private static function setSession(array $data): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $data;
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    public static function requireLogin(string $redirect = ''): void
    {
        if (!self::isLoggedIn()) {
            $cfg = require DASHBOARD_ROOT . '/config.php';
            $base = $cfg['app']['base_url'] ?? '/dashboard';
            header('Location: ' . $base . '/login.php' . ($redirect ? '?next=' . urlencode($redirect) : ''));
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            $cfg = require DASHBOARD_ROOT . '/config.php';
            header('Location: ' . ($cfg['app']['base_url'] ?? '/dashboard') . '/dashboard.php');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function canViewDepartment(string $deptCode): bool
    {
        if (self::isAdmin()) return true;
        return in_array($deptCode, $_SESSION['user']['departments'] ?? [], true);
    }

    public static function loadUsers(): array
    {
        $file = DASHBOARD_ROOT . '/data/users.json';
        if (!file_exists($file)) return [];
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    public static function saveUsers(array $users): void
    {
        $dir  = DASHBOARD_ROOT . '/data';
        $file = $dir . '/users.json';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $result = file_put_contents($file, json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($result === false) {
            throw new RuntimeException("לא ניתן לכתוב לקובץ $file — בדוק הרשאות תיקיית data/");
        }
    }
}
