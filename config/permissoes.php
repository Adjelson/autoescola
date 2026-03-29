<?php
// config/permissoes.php
// Definição de todas as permissões personalizáveis por funcionário

define('TODAS_PERMISSOES', [
    // Alunos
    'alunos_ver'      => 'Ver lista de alunos',
    'alunos_criar'    => 'Criar alunos',
    'alunos_editar'   => 'Editar alunos',
    'alunos_eliminar' => 'Eliminar alunos',

    // Receitas
    'receitas_ver'      => 'Ver receitas',
    'receitas_criar'    => 'Registar receitas',
    'receitas_eliminar' => 'Eliminar receitas',
    'receitas_reciclar' => 'Restaurar receitas (reciclagem)',

    // Despesas
    'despesas_ver'      => 'Ver despesas',
    'despesas_criar'    => 'Registar despesas',
    'despesas_editar'   => 'Editar despesas',
    'despesas_eliminar' => 'Eliminar despesas',
    'despesas_reciclar' => 'Restaurar despesas (reciclagem)',

    // Relatório e exportação
    'relatorio_ver'   => 'Ver relatório mensal',
    'exportar_excel'  => 'Exportar para Excel',
    'exportar_pdf'    => 'Exportar para PDF',
]);

// Permissões padrão para funcionário novo (conjunto base)
define('PERMISSOES_PADRAO_FUNCIONARIO', [
    'alunos_ver',
    'receitas_ver',
    'receitas_criar',
    'despesas_ver',
    'despesas_criar',
]);

/**
 * Verifica se o utilizador atual tem uma permissão específica.
 * Admin e superadmin têm sempre tudo.
 */
function temPermissao(string $perm): bool {
    $user = currentUser();
    if (in_array($user['role'] ?? '', ['superadmin', 'admin_escola'], true)) {
        return true;
    }
    $perms = $user['permissoes'] ?? [];
    if (is_string($perms)) {
        $perms = json_decode($perms, true) ?? [];
    }
    return in_array($perm, $perms, true);
}
