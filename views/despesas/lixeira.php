<?php
$pageTitle  = 'Reciclagem — Despesas';
$activePage = 'despesas';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-recycle me-2 text-warning"></i>Reciclagem — Despesas</h1>
        <p class="page-subtitle"><?= count($despesas) ?> despesa(s) eliminada(s)</p>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=despesas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar às Despesas
    </a>
</div>

<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Atenção:</strong> Os registos aqui estão excluídos e <strong>não contam</strong> para totais e relatórios.
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($despesas)): ?>
        <div class="empty-state">
            <i class="bi bi-recycle"></i>
            <p>Reciclagem vazia.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Data</th><th>Categoria</th><th>Descrição</th><th class="text-end">Valor</th><th>Eliminado em</th><th class="text-end">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($despesas as $d): ?>
                <tr class="opacity-75">
                    <td class="small"><?= date('d/m/Y', strtotime($d['data'])) ?></td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-15 text-secondary">
                            <?= CATEGORIAS_DESPESA[$d['categoria']] ?? $d['categoria'] ?>
                        </span>
                    </td>
                    <td class="small"><?= e(mb_strimwidth($d['descricao'],0,50,'…')) ?></td>
                    <td class="text-end text-muted"><?= money($d['valor']) ?></td>
                    <td class="small text-muted"><?= $d['eliminado_em'] ? date('d/m/Y H:i', strtotime($d['eliminado_em'])) : '—' ?></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=despesas&action=restore" class="d-inline"
                                  data-confirm="Restaurar esta despesa?">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=despesas&action=hardDelete" class="d-inline"
                                  data-confirm="Eliminar PERMANENTEMENTE?">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="btn btn-outline-danger"><i class="bi bi-trash-fill"></i></button>
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
