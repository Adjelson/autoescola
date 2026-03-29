<?php
$pageTitle  = 'Utilizadores';
$activePage = 'utilizadores';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

$roleBadge = [
    'superadmin'   => ['SuperAdmin',   '#7c3aed','#ede9fe'],
    'admin_escola' => ['Admin Escola', '#0284c7','#dbeafe'],
    'funcionario'  => ['Funcionário',  '#16a34a','#dcfce7'],
];
$currentUserId = currentUser()['id'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-fill-gear me-2 text-primary"></i>Utilizadores</h1>
        <p class="page-subtitle"><?= count($utilizadores) ?> utilizador(es)</p>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=utilizadores&action=create" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Novo Utilizador
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($utilizadores)): ?>
        <div class="empty-state"><i class="bi bi-people"></i><p>Nenhum utilizador.</p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Nome</th><th>Email</th><th>Role</th><th>Permissões</th><th class="text-center">Estado</th><th class="text-end">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($utilizadores as $u):
                    $badge = $roleBadge[$u['role']] ?? [$u['role'],'#6b7280','#f3f4f6'];
                ?>
                <tr class="<?= !$u['ativo'] ? 'opacity-50' : '' ?>">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0">
                                <?= strtoupper(substr($u['nome'],0,1)) ?>
                            </div>
                            <span class="fw-medium"><?= e($u['nome']) ?>
                                <?php if ((int)$u['id'] === (int)$currentUserId): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:.65rem">Você</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </td>
                    <td class="text-muted small"><?= e($u['email']) ?></td>
                    <td>
                        <span class="badge badge-tipo" style="background:<?= $badge[2] ?>;color:<?= $badge[1] ?>">
                            <?= $badge[0] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['role'] === 'funcionario'): ?>
                            <?php $np = count($u['permissoes'] ?? []); ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <?= $np ?> / <?= count(TODAS_PERMISSOES) ?> permissões
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">Acesso total</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u['ativo']): ?>
                            <span class="badge bg-success bg-opacity-15 text-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-15 text-secondary">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if ($u['role'] === 'funcionario'): ?>
                            <a href="<?= APP_URL ?>/index.php?page=utilizadores&action=editPermissoes&id=<?= $u['id'] ?>"
                               class="btn btn-outline-primary" title="Gerir permissões">
                                <i class="bi bi-shield-lock"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ((int)$u['id'] !== (int)$currentUserId && $u['role'] !== 'admin_escola'): ?>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=utilizadores&action=toggleAtivo"
                                  data-confirm="<?= $u['ativo'] ? 'Desativar' : 'Ativar' ?> este utilizador?" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn <?= $u['ativo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                    <i class="bi bi-<?= $u['ativo'] ? 'pause-circle' : 'play-circle' ?>"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
