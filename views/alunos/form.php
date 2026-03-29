<?php
$isEdit     = isset($aluno);
$pageTitle  = $isEdit ? 'Editar Aluno' : 'Novo Aluno';
$activePage = 'alunos';
require __DIR__ . '/../layouts/header.php';
?>
<?php require __DIR__ . '/../layouts/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-person-<?= $isEdit ? 'fill-gear' : 'plus' ?> me-2 text-primary"></i>
            <?= $pageTitle ?>
        </h1>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=alunos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><?= $isEdit ? 'Editar dados do aluno' : 'Dados do novo aluno' ?></div>
            <div class="card-body">
                <form method="POST"
                      action="<?= APP_URL ?>/index.php?page=alunos&action=<?= $isEdit ? 'update' : 'store' ?>"
                      novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $aluno['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome"
                               value="<?= e($aluno['nome'] ?? '') ?>"
                               placeholder="Nome do aluno" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="pacote">Categoria / Pacote *</label>
                        <select class="form-select" id="pacote" name="pacote" required>
                            <option value="">— Selecionar categoria —</option>
                            <?php foreach (PACOTES as $valor => $label): ?>
                                <option value="<?= e($valor) ?>"
                                    <?= ($aluno['pacote'] ?? '') === $valor ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="preco_total">Preço Total do Curso (€) *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="preco_total" name="preco_total"
                                   value="<?= e($aluno['preco_total'] ?? '0') ?>"
                                   min="0" step="0.01" data-type="money" required>
                            <span class="input-group-text">€</span>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            O valor pago é calculado automaticamente pelas receitas associadas a este aluno.
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="mb-4 p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="small text-muted">Preço Total</div>
                                <div class="fw-bold"><?= money($aluno['preco_total']) ?></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Valor Pago</div>
                                <div class="fw-bold text-success"><?= money($aluno['pago_total']) ?></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Dívida</div>
                                <div class="fw-bold <?= $aluno['divida'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= money(max(0, $aluno['divida'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> me-2"></i>
                            <?= $isEdit ? 'Guardar alterações' : 'Criar aluno' ?>
                        </button>
                        <a href="<?= APP_URL ?>/index.php?page=alunos" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <?php if ($isEdit): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Registar Pagamento</h6>
                <p class="small text-muted mb-3">Para registar um pagamento, crie uma receita associada a este aluno.</p>
                <a href="<?= APP_URL ?>/index.php?page=receitas&action=create&aluno_id=<?= $aluno['id'] ?>"
                   class="btn btn-outline-success btn-sm w-100">
                    <i class="bi bi-plus-circle me-2"></i>Registar Receita para este Aluno
                </a>
            </div>
        </div>
        <?php endif; ?>
        <div class="card" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="card-body">
                <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle me-2 text-primary"></i>Categorias disponíveis</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>A / A1 / A2 / AM — Motociclos</li>
                    <li>B / B1 / BE — Ligeiros</li>
                    <li>C / C1 / CE — Pesados Mercadorias</li>
                    <li>D / D1 — Pesados Passageiros</li>
                    <li>T — Trator Agrícola</li>
                    <li>Combinados (B+A, B+C, B+D)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
