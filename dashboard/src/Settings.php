<?php
class Settings
{
    private static function file(): string
    {
        return DASHBOARD_ROOT . '/data/settings.json';
    }

    public static function all(): array
    {
        $f = self::file();
        if (!file_exists($f)) return [];
        return json_decode(file_get_contents($f), true) ?? [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $dir = DASHBOARD_ROOT . '/data';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $data       = self::all();
        $data[$key] = $value;
        file_put_contents(self::file(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
