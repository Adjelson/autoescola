<?php
$pageTitle  = 'Gestão de Escolas';
$activePage = 'escolas';
require __DIR__ . '/../layouts/header.php';
?>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-buildings-fill me-2 text-primary"></i>Escolas Registadas</h1>
        <p class="page-subtitle"><?= count($escolas) ?> escola(s) no sistema</p>
    </div>
</div>

<?php if (!empty($_SESSION['superadmin_backup'])): ?>
<div class="alert alert-warning d-flex align-items-center justify-content-between">
    <span><i class="bi bi-eye-fill me-2"></i>A visualizar como utilizador de outra escola.</span>
    <form method="POST" action="<?= APP_URL ?>/index.php?page=escolas&action=stopImpersonate">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <button class="btn btn-warning btn-sm">Voltar ao SuperAdmin</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($escolas)): ?>
        <div class="empty-state">
            <i class="bi bi-buildings"></i>
            <p>Nenhuma escola registada ainda.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>NIF</th>
                        <th>Email</th>
                        <th class="text-center">Utilizadores</th>
                        <th class="text-center">Alunos</th>
                        <th>Desde</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($escolas as $escola): ?>
                <tr>
                    <td class="text-muted small"><?= $escola['id'] ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/index.php?page=escolas&action=show&id=<?= $escola['id'] ?>"
                           class="fw-semibold text-decoration-none">
                            <?= e($escola['nome']) ?>
                        </a>
                    </td>
                    <td class="text-muted small font-monospace"><?= e($escola['nif']) ?></td>
                    <td class="text-muted small"><?= e($escola['email']) ?></td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            <?= $escola['total_utilizadores'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <?= $escola['total_alunos'] ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($escola['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= APP_URL ?>/index.php?page=escolas&action=show&id=<?= $escola['id'] ?>"
                               class="btn btn-outline-primary" title="Ver detalhe">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=escolas&action=impersonate"
                                  data-confirm="Entrar na escola '<?= e($escola['nome']) ?>' como admin?" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="escola_id" value="<?= $escola['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning" title="Aceder como admin">
                                    <i class="bi bi-person-fill-up"></i>
                                </button>
                            </form>
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
