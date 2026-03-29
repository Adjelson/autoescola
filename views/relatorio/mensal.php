<?php
$pageTitle  = 'Relatório Mensal';
$activePage = 'relatorio';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

[$ano, $mesNum] = explode('-', $mes);
$nomeMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
              'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$mesNome = ($nomeMeses[(int)$mesNum] ?? $mes) . ' ' . $ano;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-file-earmark-bar-graph-fill me-2 text-primary"></i>Relatório Mensal
        </h1>
        <p class="page-subtitle"><?= $mesNome ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <?php if (temPermissao('exportar_excel')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=receitas&fmt=excel&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel Receitas
        </a>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=despesas&fmt=excel&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel Despesas
        </a>
        <?php endif; ?>
        <?php if (temPermissao('exportar_pdf')): ?>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=receitas&fmt=pdf&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF Receitas
        </a>
        <a href="<?= APP_URL ?>/index.php?page=export&tipo=despesas&fmt=pdf&mes=<?= urlencode($mes) ?>"
           class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF Despesas
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- FILTROS AVANÇADOS -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="relatorio">
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Mês</label>
                <select class="form-select form-select-sm" name="mes">
                    <?php foreach ($meses as $m): ?>
                        <?php [$y,$mo] = explode('-', $m); ?>
                        <option value="<?= $m ?>" <?= $m === $mes ? 'selected':'' ?>>
                            <?= $nomeMeses[(int)$mo] ?> <?= $y ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Tipo Receita</label>
                <select class="form-select form-select-sm" name="tipo">
                    <option value="">Todos</option>
                    <?php foreach (TIPOS_RECEITA as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($tipo??'') === $k ? 'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Método Pagamento</label>
                <select class="form-select form-select-sm" name="metodo">
                    <option value="">Todos</option>
                    <option value="numerario"     <?= ($metodo??'') === 'numerario'     ? 'selected':'' ?>>Numerário</option>
                    <option value="transferencia" <?= ($metodo??'') === 'transferencia' ? 'selected':'' ?>>Transferência</option>
                    <option value="mbway"         <?= ($metodo??'') === 'mbway'         ? 'selected':'' ?>>MBWay</option>
                    <option value="multibanco"    <?= ($metodo??'') === 'multibanco'    ? 'selected':'' ?>>Multibanco</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1 small fw-medium">Categoria Despesa</label>
                <select class="form-select form-select-sm" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach (CATEGORIAS_DESPESA as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($categoria??'') === $k ? 'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="<?= APP_URL ?>/index.php?page=relatorio" class="btn btn-outline-secondary btn-sm" title="Limpar">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div>
                <div class="stat-value"><?= money($totalReceitas) ?></div>
                <div class="stat-label">Total Receitas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div>
                <div class="stat-value"><?= money($totalDespesas) ?></div>
                <div class="stat-label">Total Despesas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon <?= $lucro >= 0 ? 'blue' : 'orange' ?>">
                <i class="bi bi-<?= $lucro >= 0 ? 'graph-up-arrow' : 'graph-down-arrow' ?>"></i>
            </div>
            <div>
                <div class="stat-value <?= $lucro >= 0 ? 'lucro-positive' : 'lucro-negative' ?>">
                    <?= money($lucro) ?>
                </div>
                <div class="stat-label"><?= $lucro >= 0 ? 'Lucro' : 'Prejuízo' ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="stat-value"><?= money($totalDivida) ?></div>
                <div class="stat-label">Total em Dívidas</div>
            </div>
        </div>
    </div>
</div>

<!-- RESULTADO LÍQUIDO -->
<div class="card mb-4 border-0" style="background: <?= $lucro >= 0 ? '#f0fdf4' : '#fff1f2' ?>; border-left: 4px solid <?= $lucro >= 0 ? '#16a34a' : '#dc2626' ?> !important; border-left-width: 4px !important;">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="fw-bold mb-1">Resultado Líquido — <?= $mesNome ?></h6>
                <p class="text-muted small mb-0">
                    Receitas <strong><?= money($totalReceitas) ?></strong> 
                    − Despesas <strong><?= money($totalDespesas) ?></strong>
                    <?php if ($totalReceitas > 0): ?>
                    · Margem: <strong><?= round(($lucro / $totalReceitas) * 100, 1) ?>%</strong>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-auto">
                <span class="display-6 fw-bold <?= $lucro >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $lucro >= 0 ? '+' : '' ?><?= money($lucro) ?>
                </span>
            </div>
        </div>
        <?php if ($totalReceitas > 0): ?>
        <div class="mt-3">
            <div class="progress" style="height:8px;border-radius:4px">
                <?php $pct = min(100, max(0, ($lucro / $totalReceitas) * 100)); ?>
                <div class="progress-bar <?= $lucro >= 0 ? 'bg-success' : 'bg-danger' ?>"
                     style="width:<?= abs($pct) ?>%"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- BREAKDOWN TABELAS -->
<div class="row g-3 mb-4">
    <!-- Receitas por tipo -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="bi bi-arrow-up-circle me-2 text-success"></i>Receitas por Tipo</span>
                <span class="badge bg-success bg-opacity-15 text-success"><?= count($receitas) ?> registos</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($receitasPorTipo)): ?>
                    <p class="text-muted text-center py-3 small">Sem receitas neste mês</p>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Tipo</th><th class="text-center">Qtd.</th><th class="text-end">Total</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                    <?php foreach ($receitasPorTipo as $r): ?>
                        <?php $pct = $totalReceitas > 0 ? round(($r['total'] / $totalReceitas) * 100, 1) : 0; ?>
                        <tr>
                            <td class="fw-medium small"><?= TIPOS_RECEITA[$r['tipo']] ?? $r['tipo'] ?></td>
                            <td class="text-center text-muted small"><?= $r['qtd'] ?></td>
                            <td class="text-end fw-semibold text-success small"><?= money($r['total']) ?></td>
                            <td class="text-end text-muted small"><?= $pct ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td>Total</td><td class="text-center"><?= count($receitas) ?></td>
                            <td class="text-end text-success"><?= money($totalReceitas) ?></td>
                            <td class="text-end">100%</td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Despesas por categoria -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="bi bi-arrow-down-circle me-2 text-danger"></i>Despesas por Categoria</span>
                <span class="badge bg-danger bg-opacity-15 text-danger"><?= count($despesas) ?> registos</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($despesasPorCategoria)): ?>
                    <p class="text-muted text-center py-3 small">Sem despesas neste mês</p>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Categoria</th><th class="text-end">Total</th><th class="text-end">%</th></tr></thead>
                    <tbody>
                    <?php foreach ($despesasPorCategoria as $d): ?>
                        <?php $pct = $totalDespesas > 0 ? round(($d['total'] / $totalDespesas) * 100, 1) : 0; ?>
                        <tr>
                            <td class="fw-medium small"><?= CATEGORIAS_DESPESA[$d['categoria']] ?? $d['categoria'] ?></td>
                            <td class="text-end fw-semibold text-danger small"><?= money($d['total']) ?></td>
                            <td class="text-end text-muted small"><?= $pct ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td>Total</td>
                            <td class="text-end text-danger"><?= money($totalDespesas) ?></td>
                            <td class="text-end">100%</td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- DETALHE RECEITAS FILTRADAS -->
<?php if (!empty($tipo) || !empty($metodo)): ?>
<div class="card mb-4">
    <div class="card-header">
        <span><i class="bi bi-arrow-up-circle me-2 text-success"></i>Receitas (filtradas)</span>
        <span class="badge bg-success bg-opacity-15 text-success"><?= count($receitas) ?> · <?= money(array_sum(array_column($receitas,'valor'))) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 table-sm">
                <thead><tr><th>Data</th><th>Aluno</th><th>Tipo</th><th>Método</th><th class="text-end">Valor</th></tr></thead>
                <tbody>
                <?php foreach ($receitas as $r): ?>
                <tr>
                    <td class="small"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                    <td class="small"><?= e($r['aluno_nome'] ?? '—') ?></td>
                    <td class="small"><?= TIPOS_RECEITA[$r['tipo']] ?? $r['tipo'] ?></td>
                    <td class="small text-muted"><?= ['numerario'=>'Numerário','transferencia'=>'Transferência','mbway'=>'MBWay','multibanco'=>'Multibanco'][$r['metodo']] ?? $r['metodo'] ?></td>
                    <td class="text-end text-success small fw-semibold"><?= money($r['valor']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DETALHE DESPESAS FILTRADAS -->
<?php if (!empty($categoria)): ?>
<div class="card mb-4">
    <div class="card-header">
        <span><i class="bi bi-arrow-down-circle me-2 text-danger"></i>Despesas — <?= CATEGORIAS_DESPESA[$categoria] ?? $categoria ?></span>
        <span class="badge bg-danger bg-opacity-15 text-danger"><?= count($despesas) ?> · <?= money(array_sum(array_column($despesas,'valor'))) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 table-sm">
                <thead><tr><th>Data</th><th>Descrição</th><th class="text-end">Valor</th></tr></thead>
                <tbody>
                <?php foreach ($despesas as $d): ?>
                <tr>
                    <td class="small"><?= date('d/m/Y', strtotime($d['data'])) ?></td>
                    <td class="small"><?= e($d['descricao']) ?></td>
                    <td class="text-end text-danger small fw-semibold"><?= money($d['valor']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ALUNOS COM DÍVIDA -->
<?php if (!empty($devedores)): ?>
<div class="card">
    <div class="card-header">
        <span><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Alunos com Dívida</span>
        <span class="text-danger fw-semibold"><?= money($totalDivida) ?> em aberto</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Aluno</th><th>Pacote</th><th class="text-end">Total</th><th class="text-end">Pago</th><th class="text-end">Dívida</th></tr></thead>
                <tbody>
                <?php foreach ($devedores as $d): ?>
                <tr>
                    <td class="fw-medium"><?= e($d['nome']) ?></td>
                    <td class="text-muted small"><?= e($d['pacote']) ?></td>
                    <td class="text-end small"><?= money($d['preco_total']) ?></td>
                    <td class="text-end text-success small"><?= money($d['pago_total']) ?></td>
                    <td class="text-end fw-bold text-danger"><?= money($d['divida']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
