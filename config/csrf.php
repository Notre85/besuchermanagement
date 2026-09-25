<?php
// config/csrf.php

if (session_status() === PHP_SESSION_NONE) {
    // Standardmäßig bleibt eine angemeldete Empfangssitzung bei Aktivität bis zu
    // acht Stunden gültig. Für Produktion kann SESSION_IDLE_TIMEOUT in der
    // externen Umgebungsdatei gesetzt werden.
    $sessionIdleTimeout = (int) ($_ENV['SESSION_IDLE_TIMEOUT'] ?? 28800);
    $sessionIdleTimeout = max(300, min($sessionIdleTimeout, 86400));
    ini_set('session.gc_maxlifetime', (string) $sessionIdleTimeout);
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (isset($_SESSION['user']) && isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > $sessionIdleTimeout) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    if (isset($_SESSION['user'])) {
        $_SESSION['last_activity'] = time();
    }
}

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

function request_rate_limited(string $scope, string $identity, int $maxRequests, int $windowSeconds): bool
{
    $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'besuchermanagement-rate-limit.json';
    $handle = @fopen($file, 'c+');
    if ($handle === false) {
        return false;
    }
    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $entries = json_decode($contents ?: '{}', true);
    $entries = is_array($entries) ? $entries : [];
    $key = hash('sha256', $scope . '|' . $identity);
    $now = time();
    $timestamps = array_values(array_filter($entries[$key] ?? [], static function ($timestamp) use ($now, $windowSeconds) {
        return is_int($timestamp) && $timestamp > $now - $windowSeconds;
    }));
    $limited = count($timestamps) >= $maxRequests;
    if (!$limited) {
        $timestamps[] = $now;
    }
    $entries[$key] = $timestamps;
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($entries, JSON_THROW_ON_ERROR));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    @chmod($file, 0600);
    return $limited;
}

function client_ip_in_networks(string $ip, string $networkList): bool
{
    $client = @inet_pton($ip);
    if ($client === false) {
        return false;
    }
    foreach (array_filter(array_map('trim', explode(',', $networkList))) as $network) {
        [$address, $prefix] = array_pad(explode('/', $network, 2), 2, null);
        $base = @inet_pton($address);
        if ($base === false || strlen($base) !== strlen($client)) {
            continue;
        }
        $prefix = $prefix === null ? strlen($client) * 8 : (int) $prefix;
        if ($prefix < 0 || $prefix > strlen($client) * 8) {
            continue;
        }
        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;
        if ($fullBytes > 0 && substr($client, 0, $fullBytes) !== substr($base, 0, $fullBytes)) {
            continue;
        }
        if ($remainingBits > 0) {
            $mask = chr((0xff << (8 - $remainingBits)) & 0xff);
            if ((ord($client[$fullBytes]) & ord($mask)) !== (ord($base[$fullBytes]) & ord($mask))) {
                continue;
            }
        }
        return true;
    }
    return false;
}
