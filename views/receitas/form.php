<?php
$pageTitle  = 'Nova Receita';
$activePage = 'receitas';
$preAluno   = (int)($_GET['aluno_id'] ?? 0);
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/flash.php';
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-plus-circle-fill me-2 text-success"></i>Nova Receita</h1>
    </div>
    <a href="<?= APP_URL ?>/index.php?page=receitas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Registar pagamento recebido</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/index.php?page=receitas&action=store" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label" for="aluno_id">Aluno (opcional)</label>
                        <select class="form-select" id="aluno_id" name="aluno_id">
                            <option value="">— Sem aluno associado —</option>
                            <?php foreach ($alunos as $a): ?>
                                <option value="<?= $a['id'] ?>"
                                    <?= $preAluno === (int)$a['id'] ? 'selected' : '' ?>>
                                    <?= e($a['nome']) ?> · <?= e($a['pacote']) ?>
                                    <?php if ((float)$a['divida'] > 0): ?>
                                        (Dívida: <?= money($a['divida']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="tipo">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Selecionar...</option>
                                <?php foreach (TIPOS_RECEITA as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="metodo">Método de Pagamento *</label>
                            <select class="form-select" id="metodo" name="metodo" required>
                                <option value="numerario">Numerário</option>
                                <option value="transferencia">Transferência Bancária</option>
                                <option value="mbway">MBWay</option>
                                <option value="multibanco">Multibanco / Referência</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="valor">Valor (€) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="valor" name="valor"
                                       min="0.01" step="0.01" placeholder="0.00" required data-type="money">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="data">Data *</label>
                            <input type="date" class="form-control" id="data" name="data"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="descricao">Observações</label>
                        <textarea class="form-control" id="descricao" name="descricao"
                                  rows="2" placeholder="Notas adicionais (opcional)"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check-lg me-2"></i>Registar Receita
                        </button>
                        <a href="<?= APP_URL ?>/index.php?page=receitas" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Tipos de Receita</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-1"><strong>Inscrição</strong> — Taxa de inscrição no curso</li>
                    <li class="mb-1"><strong>Aulas</strong> — Pagamento de aulas de condução</li>
                    <li class="mb-1"><strong>Exame</strong> — Taxa de exame teórico ou prático</li>
                    <li class="mb-1"><strong>Prestação</strong> — Pagamento faseado do curso</li>
                    <li><strong>Outro</strong> — Outros recebimentos</li>
                </ul>
            </div>
        </div>
        <div class="card mt-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="card-body">
                <p class="small text-muted mb-0">
                    <i class="bi bi-lightbulb me-2 text-warning"></i>
                    Ao associar uma receita a um aluno, o valor pago é atualizado automaticamente no perfil do aluno.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
