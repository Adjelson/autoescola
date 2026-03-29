<?php
$isEdit    = isset($despesa);
$pageTitle = $isEdit ? 'Editar Despesa' : 'Nova Despesa';
$activePage = 'despesas';
require __DIR__ . '/../layouts/header.php';

$categorias = [
    'combustivel' => 'Combustível',
    'manutencao'  => 'Manutenção',
    'salarios'    => 'Salários',
    'renda'       => 'Renda',
    'seguros'     => 'Seguros',
    'impostos'    => 'Impostos',
    'outros'      => 'Outros',
];
?>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle-fill' ?> me-2 text-danger"></i>
            <?= $pageTitle ?>
        </h1>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=despesas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><?= $isEdit ? 'Editar despesa' : 'Registar nova despesa' ?></div>
            <div class="card-body">
                <form method="POST"
                      action="<?= APP_URL ?>/index.php?page=despesas&action=<?= $isEdit ? 'update' : 'store' ?>"
                      novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $despesa['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="categoria">Categoria *</label>
                        <select class="form-select" id="categoria" name="categoria" required>
                            <option value="">Selecionar categoria...</option>
                            <?php foreach ($categorias as $k => $v): ?>
                                <option value="<?= $k ?>"
                                    <?= ($despesa['categoria'] ?? '') === $k ? 'selected' : '' ?>>
                                    <?= $v ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="valor">Valor (€) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="valor" name="valor"
                                       value="<?= e($despesa['valor'] ?? '') ?>"
                                       min="0.01" step="0.01" placeholder="0.00" required data-type="money">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="data">Data *</label>
                            <input type="date" class="form-control" id="data" name="data"
                                   value="<?= e($despesa['data'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="descricao">Descrição *</label>
                        <textarea class="form-control" id="descricao" name="descricao"
                                  rows="3" placeholder="Descreva a despesa..." required><?= e($despesa['descricao'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> me-2"></i>
                            <?= $isEdit ? 'Guardar alterações' : 'Registar Despesa' ?>
                        </button>
                        <a href="<?= APP_URL ?>/index.php?page=despesas" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
