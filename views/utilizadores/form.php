<?php
$pageTitle  = 'Novo Utilizador';
$activePage = 'utilizadores';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

$old    = $_SESSION['form_old']    ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_old'], $_SESSION['form_errors']);

$grupos = [
    'Alunos'     => ['alunos_ver','alunos_criar','alunos_editar','alunos_eliminar'],
    'Receitas'   => ['receitas_ver','receitas_criar','receitas_eliminar','receitas_reciclar'],
    'Despesas'   => ['despesas_ver','despesas_criar','despesas_editar','despesas_eliminar','despesas_reciclar'],
    'Relatório'  => ['relatorio_ver'],
    'Exportação' => ['exportar_excel','exportar_pdf'],
];
$roleSelected = $old['role'] ?? 'funcionario';
$permsOld     = $old['permissoes'] ?? PERMISSOES_PADRAO_FUNCIONARIO;
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-plus me-2 text-primary"></i>Novo Utilizador</h1>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=utilizadores" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/index.php?page=utilizadores&action=store" novalidate>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="row g-4">
        <!-- Dados básicos -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header fw-semibold">Dados do utilizador</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome"
                               value="<?= e($old['nome'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email"
                               value="<?= e($old['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="funcionario" <?= $roleSelected === 'funcionario' ? 'selected':'' ?>>
                                Funcionário
                            </option>
                            <?php if (hasRole('superadmin')): ?>
                            <option value="admin_escola" <?= $roleSelected === 'admin_escola' ? 'selected':'' ?>>
                                Admin Escola
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password"
                                   placeholder="Mín. 8 caracteres" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Confirmar *</label>
                            <input type="password" class="form-control" name="password2"
                                   placeholder="Repetir" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle me-2 text-primary"></i>Diferença de roles</h6>
                    <p class="small text-muted mb-2">
                        <strong>Admin Escola</strong> — acesso total, sem restrições.
                    </p>
                    <p class="small text-muted mb-0">
                        <strong>Funcionário</strong> — acesso controlado pelas permissões que definir ao lado.
                    </p>
                </div>
            </div>
        </div>

        <!-- Permissões (só para funcionário) -->
        <div class="col-lg-7" id="permissoesSection">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-shield-lock me-2 text-primary"></i>Permissões do Funcionário</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-xs btn-outline-success"
                                onclick="selectAllPerms(true)" style="font-size:.75rem;padding:.15rem .5rem">
                            Tudo
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-danger"
                                onclick="selectAllPerms(false)" style="font-size:.75rem;padding:.15rem .5rem">
                            Nada
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php foreach ($grupos as $grupo => $perms): ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted text-uppercase" style="letter-spacing:.04em;font-size:.72rem">
                                <?= $grupo ?>
                            </span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted"
                                    style="font-size:.72rem"
                                    onclick="toggleGrupoPerms('grupo-<?= $grupo ?>')">
                                selecionar grupo
                            </button>
                        </div>
                        <div class="row g-2" id="grupo-<?= $grupo ?>">
                            <?php foreach ($perms as $perm): ?>
                            <div class="col-sm-6">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input perm-check" type="checkbox"
                                           name="permissoes[]" value="<?= $perm ?>"
                                           id="p_<?= $perm ?>"
                                           <?= in_array($perm, (array)$permsOld) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="p_<?= $perm ?>">
                                        <?= TODAS_PERMISSOES[$perm] ?? $perm ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!array_key_last($grupos) === $grupo): ?>
                    <hr class="my-2">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-person-plus me-2"></i>Criar Utilizador
        </button>
        <a href="<?= APP_URL ?>/index.php?page=utilizadores" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script>
// Mostrar/ocultar secção de permissões conforme role
const roleSelect = document.getElementById('roleSelect');
const permsSection = document.getElementById('permissoesSection');

function updatePermsVisibility() {
    permsSection.style.display = roleSelect.value === 'funcionario' ? '' : 'none';
}

roleSelect.addEventListener('change', updatePermsVisibility);
updatePermsVisibility();

function selectAllPerms(state) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = state);
}

function toggleGrupoPerms(grupoId) {
    const cbs = document.querySelectorAll('#' + grupoId + ' .perm-check');
    const allOn = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allOn);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
