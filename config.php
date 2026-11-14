<?php
/**
 * Sozlamalarni o'qish: config('db_pass'), config('debug', false) ...
 * Qiymatlar loyiha ildizidagi config.local.php dan olinadi.
 */
function config(string $key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = dirname(__DIR__) . '/config.local.php';
        $loaded = is_file($file) ? require $file : [];
        $cfg = is_array($loaded) ? $loaded : [];
    }
    return $cfg[$key] ?? $default;
}
