<?php
// middleware/auth.php

require_once __DIR__ . '/../config/app.php';

function requireLogin(): void {
    if (!isLoggedIn()) {
        flash('warning', 'Sessão expirada. Por favor faça login.');
        redirect('index.php?page=login');
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!hasRole(...$roles)) {
        flash('danger', 'Não tem permissão para aceder a esta área.');
        redirect('index.php?page=dashboard');
    }
}

function requireSameSchool(int $escola_id): void {
    requireLogin();
    $user = currentUser();
    if ($user['role'] !== 'superadmin' && (int)($user['escola_id'] ?? 0) !== $escola_id) {
        flash('danger', 'Acesso negado.');
        redirect('index.php?page=dashboard');
    }
}

/**
 * For pages that require an active escola context.
 * Redirects superadmin (without impersonation) to the escolas page.
 */
function requireEscolaContext(): void {
    requireLogin();
    $user = currentUser();
    if ($user['role'] === 'superadmin' && empty($user['escola_id'])) {
        flash('info', 'Selecione uma escola para aceder a esta secção, ou utilize a função "Aceder como Admin".');
        redirect('index.php?page=escolas');
    }
}

/**
 * Atualiza as permissões da sessão a partir da BD.
 * Chamado após savePermissoes para o utilizador atual.
 */
function refreshSessionPermissoes(): void {
    if (!isLoggedIn()) return;
    $user = currentUser();
    if ($user['role'] !== 'funcionario') return;

    require_once __DIR__ . '/../models/User.php';
    $fresh = (new User())->findById((int)$user['id']);
    if ($fresh) {
        $perms = $fresh['permissoes'] ?? [];
        if (is_string($perms)) $perms = json_decode($perms, true) ?? [];
        $_SESSION['user']['permissoes'] = $perms;
    }
}
