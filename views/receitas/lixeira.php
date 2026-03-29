<?php
$pageTitle  = 'Reciclagem — Receitas';
$activePage = 'receitas';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-recycle me-2 text-warning"></i>Reciclagem — Receitas</h1>
        <p class="page-subtitle"><?= count($receitas) ?> receita(s) eliminada(s)</p>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=receitas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar às Receitas
    </a>
</div>

<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Atenção:</strong> Os registos aqui estão excluídos e <strong>não contam</strong> para totais e relatórios.
    Pode restaurá-los ou eliminá-los permanentemente.
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($receitas)): ?>
        <div class="empty-state">
            <i class="bi bi-recycle"></i>
            <p>Reciclagem vazia.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Data</th><th>Aluno</th><th>Tipo</th><th class="text-end">Valor</th><th>Eliminado em</th><th class="text-end">Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($receitas as $r): ?>
                <tr class="opacity-75">
                    <td class="small"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                    <td class="small"><?= e($r['aluno_nome'] ?? '—') ?></td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-15 text-secondary">
                            <?= TIPOS_RECEITA[$r['tipo']] ?? $r['tipo'] ?>
                        </span>
                    </td>
                    <td class="text-end text-muted"><?= money($r['valor']) ?></td>
                    <td class="small text-muted"><?= $r['eliminado_em'] ? date('d/m/Y H:i', strtotime($r['eliminado_em'])) : '—' ?></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=receitas&action=restore" class="d-inline"
                                  data-confirm="Restaurar esta receita?">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-outline-success" title="Restaurar">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=receitas&action=hardDelete" class="d-inline"
                                  data-confirm="Eliminar PERMANENTEMENTE? Não é possível recuperar.">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-outline-danger" title="Eliminar permanentemente">
                                    <i class="bi bi-trash-fill"></i>
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
