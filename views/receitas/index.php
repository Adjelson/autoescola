<?php
$pageTitle  = 'Receitas';
$activePage = 'receitas';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

$tipoLabels = [
    'inscricao' => ['Inscrição',  '#16a34a','#dcfce7'],
    'aulas'     => ['Aulas',      '#0284c7','#dbeafe'],
    'exame'     => ['Exame',      '#7c3aed','#ede9fe'],
    'prestacao' => ['Prestação',  '#ea580c','#ffedd5'],
    'outro'     => ['Outro',      '#6b7280','#f3f4f6'],
];
$metodosLabels = [
    'numerario'     => ['Numerário',    'bi-cash'],
    'transferencia' => ['Transferência','bi-bank'],
    'mbway'         => ['MBWay',        'bi-phone'],
    'multibanco'    => ['Multibanco',   'bi-credit-card'],
];
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-arrow-up-circle-fill me-2 text-success"></i>Receitas</h1>
        <p class="page-subtitle">Total filtrado: <strong class="text-success"><?= money($total) ?></strong></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (temPermissao('receitas_criar')): ?>
        <a href="<?= APP_URL ?>/index.php?page=receitas&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nova Receita
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_excel')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=receitas&fmt=excel&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-success btn-sm" title="Exportar Excel">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_pdf')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=receitas&fmt=pdf&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-danger btn-sm" title="Exportar PDF">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
        <?php endif; ?>
        <?php if (temPermissao('receitas_reciclar') && $totalLixeira > 0): ?>
        <a href="<?= APP_URL ?>/index.php?page=receitas&action=lixeira"
           class="btn btn-outline-secondary btn-sm position-relative" title="Reciclagem">
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
            <input type="hidden" name="page" value="receitas">
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Mês</label>
                <input type="month" class="form-control form-control-sm" name="mes" value="<?= e($mes) ?>">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Tipo</label>
                <select class="form-select form-select-sm" name="tipo">
                    <option value="">Todos</option>
                    <?php foreach (TIPOS_RECEITA as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($tipo??'') === $k ? 'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Método</label>
                <select class="form-select form-select-sm" name="metodo">
                    <option value="">Todos</option>
                    <option value="numerario"     <?= ($metodo??'') === 'numerario'     ? 'selected':'' ?>>Numerário</option>
                    <option value="transferencia" <?= ($metodo??'') === 'transferencia' ? 'selected':'' ?>>Transferência</option>
                    <option value="mbway"         <?= ($metodo??'') === 'mbway'         ? 'selected':'' ?>>MBWay</option>
                    <option value="multibanco"    <?= ($metodo??'') === 'multibanco'    ? 'selected':'' ?>>Multibanco</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Aluno</label>
                <select class="form-select form-select-sm" name="aluno_id">
                    <option value="">Todos</option>
                    <?php foreach ($alunos as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($aluno_id??'') == $a['id'] ? 'selected':'' ?>>
                            <?= e($a['nome']) ?>
                        </option>
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
            <div class="col-sm-6 col-lg-2 d-flex gap-1">
                <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="<?= APP_URL ?>/index.php?page=receitas" class="btn btn-outline-secondary btn-sm" title="Limpar">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($receitas)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Nenhuma receita encontrada para os filtros selecionados.</p>
            <?php if (temPermissao('receitas_criar')): ?>
            <a href="<?= APP_URL ?>/index.php?page=receitas&action=create" class="btn btn-primary btn-sm">Registar receita</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Data</th><th>Aluno</th><th>Tipo</th><th>Método</th>
                        <th>Descrição</th><th class="text-end">Valor</th>
                        <?php if (temPermissao('receitas_eliminar')): ?><th class="text-end">Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($receitas as $r):
                    $tl = $tipoLabels[$r['tipo']] ?? [$r['tipo'],'#6b7280','#f3f4f6'];
                    $ml = $metodosLabels[$r['metodo']] ?? [$r['metodo'],'bi-circle'];
                ?>
                <tr>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                    <td class="fw-medium small"><?= e($r['aluno_nome'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge-tipo" style="background:<?= $tl[2] ?>;color:<?= $tl[1] ?>">
                            <?= $tl[0] ?>
                        </span>
                    </td>
                    <td class="small text-muted"><i class="<?= $ml[1] ?> me-1"></i><?= $ml[0] ?></td>
                    <td class="small text-muted"><?= e($r['descricao'] ?? '') ?></td>
                    <td class="text-end fw-semibold text-success"><?= money($r['valor']) ?></td>
                    <?php if (temPermissao('receitas_eliminar')): ?>
                    <td class="text-end">
                        <form method="POST" action="<?= APP_URL ?>/index.php?page=receitas&action=delete"
                              data-confirm="Mover para reciclagem?" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="<?= temPermissao('receitas_eliminar') ? 5 : 4 ?>" class="text-end">Total:</td>
                        <td class="text-end text-success"><?= money($total) ?></td>
                        <?php if (temPermissao('receitas_eliminar')): ?><td></td><?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
