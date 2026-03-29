<?php
$pageTitle  = 'Permissões — ' . e($utilizador['nome']);
$activePage = 'utilizadores';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

$grupos = [
    'Alunos'      => ['alunos_ver','alunos_criar','alunos_editar','alunos_eliminar'],
    'Receitas'    => ['receitas_ver','receitas_criar','receitas_eliminar','receitas_reciclar'],
    'Despesas'    => ['despesas_ver','despesas_criar','despesas_editar','despesas_eliminar','despesas_reciclar'],
    'Relatório'   => ['relatorio_ver'],
    'Exportação'  => ['exportar_excel','exportar_pdf'],
];
$permsAtivas = $utilizador['permissoes'] ?? [];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-shield-lock-fill me-2 text-primary"></i>
            Permissões — <?= e($utilizador['nome']) ?>
        </h1>
        <p class="page-subtitle">Funcionário · <?= e($utilizador['email']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=utilizadores" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="<?= APP_URL ?>/index.php?page=utilizadores&action=savePermissoes">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $utilizador['id'] ?>">

            <?php foreach ($grupos as $grupo => $perms): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <span class="fw-semibold"><?= $grupo ?></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="toggleGrupo('<?= $grupo ?>')">
                        Selecionar tudo
                    </button>
                </div>
                <div class="card-body" id="grupo-<?= $grupo ?>">
                    <div class="row g-2">
                        <?php foreach ($perms as $perm): ?>
                        <div class="col-sm-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input perm-<?= e($grupo) ?>" type="checkbox"
                                       name="permissoes[]" value="<?= e($perm) ?>"
                                       id="perm_<?= $perm ?>"
                                       <?= in_array($perm, $permsAtivas) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="perm_<?= $perm ?>">
                                    <?= e(TODAS_PERMISSOES[$perm] ?? $perm) ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-2"></i>Guardar Permissões
                </button>
                <a href="<?= APP_URL ?>/index.php?page=utilizadores" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Sobre Permissões</h6>
                <p class="small text-muted">
                    As permissões apenas se aplicam a utilizadores com o role <strong>Funcionário</strong>.
                    Administradores têm sempre acesso total.
                </p>
                <p class="small text-muted mb-0">
                    Ao desativar uma permissão, o botão/página correspondente desaparece da interface do funcionário.
                </p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Ações rápidas</h6>
                <div class="d-flex gap-2 flex-column">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="selectAll(true)">
                        <i class="bi bi-check-all me-1"></i>Ativar tudo
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="selectAll(false)">
                        <i class="bi bi-x-lg me-1"></i>Desativar tudo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAll(state) {
    document.querySelectorAll('input[name="permissoes[]"]').forEach(cb => cb.checked = state);
}
function toggleGrupo(grupo) {
    const cbs = document.querySelectorAll('.perm-' + grupo);
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
