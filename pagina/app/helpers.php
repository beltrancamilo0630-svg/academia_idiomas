<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Escapa texto para mostrarlo en HTML.
 */
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige y detiene la ejecución.
 */
function redirect_to(string $url): void {
    header('Location: ' . $url);
    exit();
}

/**
 * Exige una sesión activa y uno de los id_rol permitidos.
 */
function require_role(array $allowedRoles): void {
    if (empty($_SESSION['usuario']) || !isset($_SESSION['id_rol'])) {
        redirect_to('login.php');
    }

    if (!in_array((int) $_SESSION['id_rol'], $allowedRoles, true)) {
        redirect_to('login.php');
    }
}

/**
 * Token CSRF de la sesión.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Campo oculto con el token CSRF.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verifica un token CSRF recibido.
 */
function verify_csrf(?string $token): bool {
    return is_string($token)
        && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Guarda un mensaje flash para la siguiente petición.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Lee y elimina el mensaje flash actual.
 */
function get_flash(): ?array {
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Iniciales de un nombre para los avatares.
 */
function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= function_exists('mb_substr')
            ? mb_substr($part, 0, 1, 'UTF-8')
            : substr($part, 0, 1);
    }

    return function_exists('mb_strtoupper')
        ? mb_strtoupper($initials, 'UTF-8')
        : strtoupper($initials);
}

/**
 * Clase de bandera según el idioma del curso.
 */
function flag_class(string $courseName): string {
    $name = function_exists('mb_strtolower')
        ? mb_strtolower($courseName, 'UTF-8')
        : strtolower($courseName);

    if (strpos($name, 'franc') !== false) {
        return 'fr';
    }
    if (strpos($name, 'portug') !== false) {
        return 'pt';
    }
    if (strpos($name, 'italian') !== false) {
        return 'it';
    }

    return 'en';
}

/**
 * Formatea un precio en pesos colombianos.
 */
function format_cop($amount): string {
    return '$' . number_format((float) $amount, 0, ',', '.');
}

/**
 * Formatea una fecha de MySQL como dd/mm/aaaa.
 */
function format_date($date): string {
    if (empty($date)) {
        return '—';
    }

    $timestamp = strtotime((string) $date);

    return $timestamp ? date('d/m/Y', $timestamp) : '—';
}
