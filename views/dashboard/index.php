<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';

// Verificar permissões (disponíveis via config/permissoes.php carregado no index.php)
$podeVerReceitas  = temPermissao('receitas_ver');
$podeVerDespesas  = temPermissao('despesas_ver');
$podeVerAlunos    = temPermissao('alunos_ver');
$podeVerRelatorio = temPermissao('relatorio_ver');
$podeVerAmbos     = $podeVerReceitas && $podeVerDespesas;
$ehAdmin          = hasRole('admin_escola', 'superadmin');

$catLabels = [
    'combustivel' => ['Combustível', 'bi-fuel-pump',    '#f59e0b'],
    'manutencao'  => ['Manutenção',  'bi-tools',        '#3b82f6'],
    'salarios'    => ['Salários',    'bi-person-badge', '#8b5cf6'],
    'renda'       => ['Renda',       'bi-house',        '#ec4899'],
    'seguros'     => ['Seguros',     'bi-shield-check', '#06b6d4'],
    'impostos'    => ['Impostos',    'bi-receipt',      '#ef4444'],
    'outros'      => ['Outros',      'bi-three-dots',   '#6b7280'],
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">
            <?= date('F Y') ?>
            <?php if (!empty($isSuperadminGlobal)): ?>
                &mdash; <span class="badge" style="background:#ede9fe;color:#7c3aed;font-size:.75rem">
                    Visão Global · <?= $totalEscolas ?> escola(s)
                </span>
            <?php else: ?>
                &mdash; <?= e(currentUser()['escola_nome'] ?? '') ?>
            <?php endif; ?>
        </p>
    </div>
    <?php if ($podeVerRelatorio && !empty($isSuperadminGlobal) === false): ?>
    <a href="<?= APP_URL ?>/index.php?page=relatorio" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-bar-graph me-1"></i>Ver Relatório
    </a>
    <?php endif; ?>
</div>

<?php
// Aviso quando funcionário tem permissões muito limitadas
if (!$ehAdmin && !$podeVerReceitas && !$podeVerDespesas && !$podeVerAlunos):
?>
<div class="alert alert-info d-flex align-items-center gap-3">
    <i class="bi bi-shield-lock-fill fs-4 text-primary flex-shrink-0"></i>
    <div>
        <strong>Acesso limitado</strong> — A sua conta tem permissões restritas. 
        Contacte o administrador para mais acessos.
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     STAT CARDS — mostrar apenas o que o utilizador vê
     ══════════════════════════════════════════════════════ -->
<?php
$cards = [];

if ($podeVerReceitas) {
    $cards[] = ['green', 'bi-arrow-up-circle-fill',
        money($totalReceitas ?? 0), 'Receitas (mês)',
        APP_URL . '/index.php?page=receitas'];
}
if ($podeVerDespesas) {
    $cards[] = ['red', 'bi-arrow-down-circle-fill',
        money($totalDespesas ?? 0), 'Despesas (mês)',
        APP_URL . '/index.php?page=despesas'];
}
if ($podeVerAmbos) {
    $lv = $lucro ?? 0;
    $cards[] = [$lv >= 0 ? 'blue' : 'orange',
        'bi-' . ($lv >= 0 ? 'graph-up-arrow' : 'graph-down-arrow'),
        money($lv),
        $lv >= 0 ? 'Lucro (mês)' : 'Prejuízo (mês)',
        null];
}
if ($podeVerAlunos) {
    $cards[] = ['orange', 'bi-exclamation-triangle-fill',
        $alunosComDivida ?? 0, 'Alunos c/ dívida',
        APP_URL . '/index.php?page=alunos'];
}
?>

<?php if (!empty($cards)): ?>
<div class="row g-3 mb-4">
    <?php
    // Calcular coluna ideal conforme número de cards
    $n = count($cards);
    $col = match(true) {
        $n >= 4 => 'col-sm-6 col-xl-3',
        $n === 3 => 'col-sm-6 col-lg-4',
        $n === 2 => 'col-sm-6',
        default  => 'col-12 col-sm-8 col-md-6',
    };
    ?>
    <?php foreach ($cards as [$iconColor, $icon, $valor, $label, $link]): ?>
    <div class="<?= $col ?>">
        <?php if ($link): ?>
        <a href="<?= $link ?>" class="text-decoration-none">
        <?php endif; ?>
        <div class="stat-card <?= $link ? 'stat-card-link' : '' ?>">
            <div class="stat-icon <?= $iconColor ?>">
                <i class="bi <?= $icon ?>"></i>
            </div>
            <div>
                <div class="stat-value"><?= $valor ?></div>
                <div class="stat-label"><?= $label ?></div>
            </div>
        </div>
        <?php if ($link): ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     SECÇÃO ALUNOS COM DÍVIDA + DESPESAS POR CATEGORIA
     ══════════════════════════════════════════════════════ -->
<?php $temSecundario = $podeVerAlunos || $podeVerDespesas; ?>
<?php if ($temSecundario): ?>
<div class="row g-3 mb-4">

    <?php if ($podeVerAlunos): ?>
    <div class="<?= $podeVerDespesas ? 'col-lg-7' : 'col-12' ?>">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="bi bi-people-fill me-2 text-danger"></i>Alunos com Dívida</span>
                <a href="<?= APP_URL ?>/index.php?page=alunos" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topDevedores)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-3 text-success d-block mb-2"></i>
                        Sem dívidas em aberto!
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <?php if (!empty($isSuperadminGlobal)): ?>
                                <th>Escola</th>
                                <?php endif; ?>
                                <th>Pacote</th>
                                <?php if ($podeVerReceitas): ?>
                                <th class="text-end">Dívida</th>
                                <th class="text-end">Progresso</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($topDevedores, 0, 6) as $d):
                            $pct = $d['preco_total'] > 0
                                ? round(($d['pago_total'] / $d['preco_total']) * 100)
                                : 0;
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($isSuperadminGlobal)): ?>
                                        <span class="fw-semibold"><?= e($d['nome']) ?></span>
                                    <?php elseif (temPermissao('alunos_editar')): ?>
                                        <a href="<?= APP_URL ?>/index.php?page=alunos&action=edit&id=<?= $d['id'] ?>"
                                           class="text-decoration-none fw-semibold">
                                            <?= e($d['nome']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="fw-semibold"><?= e($d['nome']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($isSuperadminGlobal)): ?>
                                <td class="text-muted small"><?= e($d['escola_nome'] ?? '') ?></td>
                                <?php endif; ?>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <?= e($d['pacote']) ?>
                                    </span>
                                </td>
                                <?php if ($podeVerReceitas): ?>
                                <td class="text-end text-danger fw-semibold">
                                    <?= money($d['divida']) ?>
                                </td>
                                <td class="text-end" style="min-width:90px">
                                    <small class="text-muted"><?= $pct ?>%</small>
                                    <div class="debt-bar">
                                        <div class="debt-bar-fill" style="width:<?= $pct ?>%"></div>
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
    </div>
    <?php endif; ?>

    <?php if ($podeVerDespesas): ?>
    <div class="<?= $podeVerAlunos ? 'col-lg-5' : 'col-12 col-md-6' ?>">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="bi bi-pie-chart-fill me-2 text-warning"></i>Despesas por Categoria</span>
                <a href="<?= APP_URL ?>/index.php?page=despesas" class="btn btn-sm btn-outline-secondary btn-sm">
                    Ver todas
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($despesasPorCategoria)): ?>
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Sem despesas este mês
                    </div>
                <?php else: ?>
                    <?php foreach ($despesasPorCategoria as $cat):
                        $info = $catLabels[$cat['categoria']] ?? [$cat['categoria'], 'bi-circle', '#6b7280'];
                    ?>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="<?= $info[1] ?>" style="color:<?= $info[2] ?>"></i>
                            <span class="small fw-medium"><?= $info[0] ?></span>
                        </div>
                        <span class="fw-semibold small"><?= money($cat['total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($podeVerReceitas && $totalDespesas > 0): ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Total despesas</span>
                        <span class="fw-bold text-danger"><?= money($totalDespesas ?? 0) ?></span>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     ÚLTIMAS RECEITAS + ÚLTIMAS DESPESAS
     ══════════════════════════════════════════════════════ -->
<?php $temTabelas = $podeVerReceitas || $podeVerDespesas; ?>
<?php if ($temTabelas): ?>
<div class="row g-3">

    <?php if ($podeVerReceitas): ?>
    <div class="<?= $podeVerDespesas ? 'col-lg-6' : 'col-12' ?>">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-arrow-up-circle me-2 text-success"></i>Últimas Receitas</span>
                <div class="d-flex gap-1 align-items-center">
                    <?php if (temPermissao('receitas_criar')): ?>
                    <a href="<?= APP_URL ?>/index.php?page=receitas&action=create"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-plus"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/index.php?page=receitas"
                       class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimasReceitas)): ?>
                    <div class="empty-state" style="padding:2rem">
                        <i class="bi bi-inbox" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.5rem"></i>
                        <p class="small text-muted mb-0">Sem receitas este mês</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Aluno</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimasReceitas as $r): ?>
                        <tr>
                            <td class="text-muted small"><?= date('d/m', strtotime($r['data'])) ?></td>
                            <td class="small"><?= e($r['aluno_nome'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-tipo" style="background:#dcfce7;color:#166534">
                                    <?= TIPOS_RECEITA[$r['tipo']] ?? ucfirst($r['tipo']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold text-success"><?= money($r['valor']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($podeVerDespesas): ?>
    <div class="<?= $podeVerReceitas ? 'col-lg-6' : 'col-12' ?>">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-arrow-down-circle me-2 text-danger"></i>Últimas Despesas</span>
                <div class="d-flex gap-1 align-items-center">
                    <?php if (temPermissao('despesas_criar')): ?>
                    <a href="<?= APP_URL ?>/index.php?page=despesas&action=create"
                       class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-plus"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/index.php?page=despesas"
                       class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($ultimasDespesas)): ?>
                    <div class="empty-state" style="padding:2rem">
                        <i class="bi bi-inbox" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.5rem"></i>
                        <p class="small text-muted mb-0">Sem despesas este mês</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimasDespesas as $d): ?>
                        <tr>
                            <td class="text-muted small"><?= date('d/m', strtotime($d['data'])) ?></td>
                            <td class="small"><?= e(mb_strimwidth($d['descricao'], 0, 28, '…')) ?></td>
                            <td>
                                <span class="badge badge-tipo" style="background:#fee2e2;color:#991b1b">
                                    <?= CATEGORIAS_DESPESA[$d['categoria']] ?? ucfirst($d['categoria']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold text-danger"><?= money($d['valor']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     ESTADO SEM PERMISSÕES — dashboard vazio com guia
     ══════════════════════════════════════════════════════ -->
<?php if (!$podeVerReceitas && !$podeVerDespesas && !$podeVerAlunos && !$ehAdmin): ?>
<div class="card mt-2">
    <div class="card-body text-center py-5">
        <i class="bi bi-shield-lock" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:1rem"></i>
        <h5 class="fw-semibold mb-2">Dashboard sem dados</h5>
        <p class="text-muted small mb-0">
            O seu perfil não tem permissão para ver receitas, despesas ou alunos.<br>
            Contacte o administrador para ajustar as suas permissões.
        </p>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
