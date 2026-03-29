<?php
$pageTitle  = 'Alunos';
$activePage = 'alunos';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill me-2 text-primary"></i>Alunos</h1>
        <p class="page-subtitle"><?= count($alunos) ?> aluno(s) registado(s)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (temPermissao('alunos_criar')): ?>
        <a href="<?= APP_URL ?>/index.php?page=alunos&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Novo Aluno
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_excel')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=alunos&fmt=excel"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_pdf')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=alunos&fmt=pdf"
           class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="page" value="alunos">
            <div class="input-group" style="max-width:340px">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" name="q" placeholder="Pesquisar aluno ou categoria..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-outline-primary btn-sm">Pesquisar</button>
            <?php if (!empty($search)): ?>
                <a href="<?= APP_URL ?>/index.php?page=alunos" class="btn btn-outline-secondary btn-sm">Limpar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($alunos)): ?>
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <p class="mb-2">Nenhum aluno encontrado.</p>
            <?php if (temPermissao('alunos_criar')): ?>
            <a href="<?= APP_URL ?>/index.php?page=alunos&action=create" class="btn btn-primary btn-sm">
                Adicionar primeiro aluno
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Nome</th><th>Categoria / Pacote</th>
                        <th class="text-end">Preço Total</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">Dívida</th>
                        <th class="text-center">Estado</th>
                        <?php if (temPermissao('alunos_editar') || temPermissao('alunos_eliminar')): ?>
                        <th class="text-end">Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alunos as $a):
                    $divida = max(0, (float)$a['divida']);
                ?>
                <tr>
                    <td class="text-muted small"><?= $a['id'] ?></td>
                    <td class="fw-semibold"><?= e($a['nome']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= e($a['pacote']) ?></span></td>
                    <td class="text-end"><?= money($a['preco_total']) ?></td>
                    <td class="text-end text-success fw-medium"><?= money($a['pago_total']) ?></td>
                    <td class="text-end fw-semibold <?= $divida > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $divida > 0 ? money($divida) : '<i class="bi bi-check-circle-fill text-success"></i>' ?>
                    </td>
                    <td class="text-center">
                        <?php if ($divida <= 0): ?>
                            <span class="badge bg-success bg-opacity-15 text-success">Pago</span>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-15 text-danger">Em dívida</span>
                        <?php endif; ?>
                    </td>
                    <?php if (temPermissao('alunos_editar') || temPermissao('alunos_eliminar')): ?>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if (temPermissao('alunos_editar')): ?>
                            <a href="<?= APP_URL ?>/index.php?page=alunos&action=edit&id=<?= $a['id'] ?>"
                               class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (temPermissao('alunos_eliminar')): ?>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=alunos&action=delete"
                                  data-confirm="Eliminar aluno '<?= e($a['nome']) ?>'?" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
