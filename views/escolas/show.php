<?php
$pageTitle  = e($escola['nome']);
$activePage = 'escolas';
require __DIR__ . '/../layouts/header.php';

$nomeMeses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
              'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
[$ano, $mesNum] = explode('-', $mes);
$mesNome = ($nomeMeses[(int)$mesNum] ?? $mes) . ' ' . $ano;

$roleBadge = [
    'admin_escola' => ['Admin','#0284c7','#dbeafe'],
    'funcionario'  => ['Func.','#16a34a','#dcfce7'],
];
?>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i><?= e($escola['nome']) ?></h1>
        <p class="page-subtitle">NIF: <?= e($escola['nif']) ?> &nbsp;·&nbsp; <?= e($escola['email']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=escolas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div>
                <div class="stat-value"><?= money($totalReceitas) ?></div>
                <div class="stat-label">Receitas <?= $mesNome ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div>
                <div class="stat-value"><?= money($totalDespesas) ?></div>
                <div class="stat-label">Despesas <?= $mesNome ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value"><?= $totalAlunos ?></div>
                <div class="stat-label">Total Alunos</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <div class="stat-value"><?= $alunosDevedores ?></div>
                <div class="stat-label">Alunos c/ Dívida</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Utilizadores -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-people me-2"></i>Utilizadores</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= count($utilizadores) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($utilizadores)): ?>
                    <p class="text-muted text-center py-3">Sem utilizadores</p>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Nome</th><th>Email</th><th>Role</th><th class="text-center">Ativo</th></tr></thead>
                    <tbody>
                    <?php foreach ($utilizadores as $u):
                        $rb = $roleBadge[$u['role']] ?? [$u['role'],'#6b7280','#f3f4f6'];
                    ?>
                    <tr>
                        <td class="fw-medium small"><?= e($u['nome']) ?></td>
                        <td class="text-muted small"><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge badge-tipo" style="background:<?= $rb[2] ?>;color:<?= $rb[1] ?>">
                                <?= $rb[0] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($u['ativo']): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            <?php else: ?>
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Acesso rápido -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-lightning-charge me-2 text-warning"></i>Acesso Rápido</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Como SuperAdmin pode entrar na escola e visualizar/gerir os dados como se fosse o admin da escola.
                </p>
                <form method="POST" action="<?= APP_URL ?>/index.php?page=escolas&action=impersonate"
                      data-confirm="Entrar na escola '<?= e($escola['nome']) ?>' como admin?">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="escola_id" value="<?= $escola['id'] ?>">
                    <button type="submit" class="btn btn-warning fw-semibold w-100">
                        <i class="bi bi-person-fill-up me-2"></i>Aceder como Admin desta Escola
                    </button>
                </form>
                <hr>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 text-center rounded" style="background:#f9fafb;border:1px solid #e5e7eb">
                            <div class="fw-bold fs-5"><?= money($totalReceitas - $totalDespesas) ?></div>
                            <small class="text-muted">Lucro <?= $mesNome ?></small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 text-center rounded" style="background:#f9fafb;border:1px solid #e5e7eb">
                            <div class="fw-bold fs-5"><?= date('d/m/Y', strtotime($escola['created_at'])) ?></div>
                            <small class="text-muted">Data de Registo</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
