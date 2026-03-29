<?php
// config/app.php

define('APP_NAME', 'AutoEscola Financeiro');
define('APP_URL', 'http://localhost/autoescola');
define('APP_VERSION', '2.0.0');

// Categorias de veículos / pacotes
define('PACOTES', [
    'Categoria A'          => 'Categoria A — Motociclo',
    'Categoria A1'         => 'Categoria A1 — Motociclo ligeiro',
    'Categoria A2'         => 'Categoria A2 — Motociclo intermédio',
    'Categoria AM'         => 'Categoria AM — Ciclomotor',
    'Categoria B'          => 'Categoria B — Ligeiro Passageiros',
    'Categoria B1'         => 'Categoria B1 — Quadriciclo',
    'Categoria BE'         => 'Categoria BE — Ligeiro + Reboque',
    'Categoria C'          => 'Categoria C — Pesado Mercadorias',
    'Categoria C1'         => 'Categoria C1 — Pesado Mercadorias Médio',
    'Categoria CE'         => 'Categoria CE — Pesado Mercadorias + Reboque',
    'Categoria D'          => 'Categoria D — Pesado Passageiros',
    'Categoria D1'         => 'Categoria D1 — Minibus',
    'Categoria T'          => 'Categoria T — Trator Agrícola',
    'Categoria B + A'      => 'Categoria B + A (combinado)',
    'Categoria B + C'      => 'Categoria B + C (combinado)',
    'Categoria B + D'      => 'Categoria B + D (combinado)',
]);

// Tipos de receita
define('TIPOS_RECEITA', [
    'inscricao' => 'Inscrição',
    'aulas'     => 'Aulas',
    'exame'     => 'Exame',
    'prestacao' => 'Prestação',
    'outro'     => 'Outro',
]);

// Categorias de despesa
define('CATEGORIAS_DESPESA', [
    'combustivel' => 'Combustível',
    'manutencao'  => 'Manutenção',
    'salarios'    => 'Salários',
    'renda'       => 'Renda',
    'seguros'     => 'Seguros',
    'impostos'    => 'Impostos',
    'outros'      => 'Outros',
]);

// Iniciar sessão segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// ---- Helpers globais ----

function redirect(string $path): void {
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

function hasRole(string ...$roles): bool {
    $user = currentUser();
    return in_array($user['role'] ?? '', $roles, true);
}

function escolarId(): ?int {
    $user = currentUser();
    return isset($user['escola_id']) ? (int)$user['escola_id'] : null;
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function e(mixed $val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(mixed $val): string {
    return number_format((float)$val, 2, ',', '.') . ' €';
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token inválido.');
    }
}
