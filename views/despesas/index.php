<?php
$pageTitle  = 'Despesas';
$activePage = 'despesas';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

$catInfo = [
    'combustivel' => ['bi-fuel-pump',   '#f59e0b','#fef3c7'],
    'manutencao'  => ['bi-tools',       '#3b82f6','#dbeafe'],
    'salarios'    => ['bi-person-badge','#8b5cf6','#ede9fe'],
    'renda'       => ['bi-house',       '#ec4899','#fce7f3'],
    'seguros'     => ['bi-shield-check','#06b6d4','#cffafe'],
    'impostos'    => ['bi-receipt',     '#ef4444','#fee2e2'],
    'outros'      => ['bi-three-dots',  '#6b7280','#f3f4f6'],
];
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-arrow-down-circle-fill me-2 text-danger"></i>Despesas</h1>
        <p class="page-subtitle">Total filtrado: <strong class="text-danger"><?= money($total) ?></strong></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (temPermissao('despesas_criar')): ?>
        <a href="<?= APP_URL ?>/index.php?page=despesas&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nova Despesa
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_excel')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=despesas&fmt=excel&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_pdf')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=despesas&fmt=pdf&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <?php endif; ?>
        <?php if (temPermissao('despesas_reciclar') && $totalLixeira > 0): ?>
        <a href="<?= APP_URL ?>/index.php?page=despesas&action=lixeira"
           class="btn btn-outline-secondary btn-sm position-relative">
            <i class="bi bi-recycle me-1"></i>Reciclagem
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                <?= $totalLixeira ?>
            </span>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros avançados -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="despesas">
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Mês</label>
                <input type="month" class="form-control form-control-sm" name="mes" value="<?= e($mes) ?>">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Categoria</label>
                <select class="form-select form-select-sm" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach (CATEGORIAS_DESPESA as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($categoria??'') === $k ? 'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Valor (€)</label>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" name="valor_min" placeholder="Mín"
                           step="0.01" value="<?= e($valor_min??'') ?>">
                    <input type="number" class="form-control" name="valor_max" placeholder="Máx"
                           step="0.01" value="<?= e($valor_max??'') ?>">
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label mb-1 small fw-medium">Pesquisar descrição</label>
                <input type="text" class="form-control form-control-sm" name="q"
                       placeholder="Pesquisar..." value="<?= e($q??'') ?>">
            </div>
            <div class="col-sm-6 col-lg-2 d-flex gap-1">
                <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="<?= APP_URL ?>/index.php?page=despesas" class="btn btn-outline-secondary btn-sm" title="Limpar">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($despesas)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Nenhuma despesa encontrada.</p>
            <?php if (temPermissao('despesas_criar')): ?>
            <a href="<?= APP_URL ?>/index.php?page=despesas&action=create" class="btn btn-primary btn-sm">Registar despesa</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Data</th><th>Categoria</th><th>Descrição</th><th class="text-end">Valor</th>
                    <?php if (temPermissao('despesas_editar') || temPermissao('despesas_eliminar')): ?>
                    <th class="text-end">Ações</th>
                    <?php endif; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($despesas as $d):
                    $ci = $catInfo[$d['categoria']] ?? ['bi-circle','#6b7280','#f3f4f6'];
                ?>
                <tr>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($d['data'])) ?></td>
                    <td>
                        <span class="badge badge-tipo d-inline-flex align-items-center gap-1"
                              style="background:<?= $ci[2] ?>;color:<?= $ci[1] ?>">
                            <i class="<?= $ci[0] ?>"></i>
                            <?= CATEGORIAS_DESPESA[$d['categoria']] ?? $d['categoria'] ?>
                        </span>
                    </td>
                    <td class="small"><?= e($d['descricao']) ?></td>
                    <td class="text-end fw-semibold text-danger"><?= money($d['valor']) ?></td>
                    <?php if (temPermissao('despesas_editar') || temPermissao('despesas_eliminar')): ?>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if (temPermissao('despesas_editar')): ?>
                            <a href="<?= APP_URL ?>/index.php?page=despesas&action=edit&id=<?= $d['id'] ?>"
                               class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (temPermissao('despesas_eliminar')): ?>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=despesas&action=delete"
                                  data-confirm="Mover para reciclagem?" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total:</td>
                        <td class="text-end text-danger"><?= money($total) ?></td>
                        <?php if (temPermissao('despesas_editar') || temPermissao('despesas_eliminar')): ?><td></td><?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
