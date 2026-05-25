<?php

const DEPARTMENTS = [
    '10' => 'היסטוריה ופילוסופיה',
    '30' => 'לימודי יסוד בהנדסה',
    '31' => 'הנדסת תעשיה וניהול',
    '33' => 'הנדסה כימית',
    '34' => 'פלסטיקה',
    '35' => 'הנדסת תוכנה',
    '36' => 'הנדסת אלקטרוניקה',
    '39' => 'לימודי יסוד בהנדסה',
    '40' => 'היסטוריה ופילוסופיה',
    '41' => 'עיצוב אופנה',
    '42' => 'עיצוב טכסטיל',
    '43' => 'עיצוב תכשיטים',
    '44' => 'תקשורת חזותית',
    '45' => 'עיצוב תעשייתי',
    '46' => 'עיצוב מבנה וסביבה',
    '47' => 'אמנות רב תחומית',
];

function get_dept_code_from_shortname(string $shortname): string
{
    $parts = explode('_', $shortname);
    return (count($parts) >= 3) ? substr($parts[2], 0, 2) : '';
}

function get_dept_name_from_shortname(string $shortname): string
{
    $code = get_dept_code_from_shortname($shortname);
    return DEPARTMENTS[$code] ?? 'כללי';
}

function get_semester_key(string $shortname): string
{
    $parts = explode('_', $shortname, 3);
    return (count($parts) >= 2) ? $parts[0] . '_' . $parts[1] : '';
}

/**
 * Returns status info based on days since last access.
 * Boundary: 0-30 = active, 31-90 = needs follow-up, 91+ or null = inactive
 */
function get_activity_status(?int $days): array
{
    if ($days === null) {
        return ['label' => 'לא פעיל', 'class' => 'status-inactive', 'color' => '#ef4444'];
    }
    if ($days <= 30) {
        return ['label' => 'פעיל', 'class' => 'status-active', 'color' => '#10b981'];
    }
    if ($days <= 90) {
        return ['label' => 'דורש מעקב', 'class' => 'status-warning', 'color' => '#f59e0b'];
    }
    return ['label' => 'לא פעיל', 'class' => 'status-inactive', 'color' => '#ef4444'];
}

function format_days(?int $days): string
{
    return $days === null ? '—' : $days . ' ימים';
}

function format_minutes(float $minutes): string
{
    if ($minutes < 1) {
        return round($minutes * 60) . ' שניות';
    }
    if ($minutes < 60) {
        return number_format($minutes, 1) . ' דקות';
    }
    $h = floor($minutes / 60);
    $m = round($minutes - $h * 60);
    return $h . 'ש\' ' . $m . 'ד\'';
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function json_response(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
