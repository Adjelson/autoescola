<?php
// controllers/DespesasController.php

require_once __DIR__ . '/../models/Despesa.php';
require_once __DIR__ . '/../middleware/auth.php';

class DespesasController {

    public function index(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_ver')) { flash('danger','Sem permissão.'); redirect('index.php?page=dashboard'); }
        $escola_id    = escolarId();
        $mes          = $_GET['mes']       ?? date('Y-m');
        $categoria    = $_GET['categoria'] ?? '';
        $valor_min    = $_GET['valor_min'] ?? '';
        $valor_max    = $_GET['valor_max'] ?? '';
        $q            = $_GET['q']         ?? '';
        $model        = new Despesa();
        $despesas     = $model->list($escola_id, array_filter(compact('mes','categoria','valor_min','valor_max','q')));
        $total        = array_sum(array_column($despesas, 'valor'));
        $totalLixeira = $model->countLixeira($escola_id);
        require __DIR__ . '/../views/despesas/index.php';
    }

    public function lixeira(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        $escola_id = escolarId();
        $despesas  = (new Despesa())->list($escola_id, [], true);
        require __DIR__ . '/../views/despesas/lixeira.php';
    }

    public function create(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        require __DIR__ . '/../views/despesas/form.php';
    }

    public function store(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        verifyCsrf();

        $escola_id = escolarId();
        $categoria = $_POST['categoria'] ?? '';
        $valor     = (float)str_replace(',', '.', $_POST['valor'] ?? 0);
        $data      = $_POST['data']      ?? '';
        $descricao = trim($_POST['descricao'] ?? '');

        if (!array_key_exists($categoria, CATEGORIAS_DESPESA) || $valor <= 0 || empty($data) || empty($descricao)) {
            flash('danger', 'Preencha todos os campos corretamente.');
            redirect('index.php?page=despesas&action=create');
        }

        (new Despesa())->create(compact('escola_id','categoria','valor','data','descricao'));
        flash('success', 'Despesa registada.');
        redirect('index.php?page=despesas');
    }

    public function edit(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_editar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        $escola_id = escolarId();
        $id        = (int)($_GET['id'] ?? 0);
        $despesa   = (new Despesa())->findById($id, $escola_id);
        if (!$despesa) { flash('danger', 'Despesa não encontrada.'); redirect('index.php?page=despesas'); }
        require __DIR__ . '/../views/despesas/form.php';
    }

    public function update(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_editar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        verifyCsrf();
        $escola_id = escolarId();
        $id        = (int)($_POST['id'] ?? 0);
        $categoria = $_POST['categoria'] ?? '';
        $valor     = (float)str_replace(',', '.', $_POST['valor'] ?? 0);
        $data      = $_POST['data']      ?? '';
        $descricao = trim($_POST['descricao'] ?? '');
        (new Despesa())->update($id, $escola_id, compact('categoria','valor','data','descricao'));
        flash('success', 'Despesa atualizada.');
        redirect('index.php?page=despesas');
    }

    public function delete(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_eliminar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        verifyCsrf();
        (new Despesa())->softDelete((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Despesa movida para a reciclagem.');
        redirect('index.php?page=despesas');
    }

    public function restore(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        verifyCsrf();
        (new Despesa())->restore((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Despesa restaurada.');
        redirect('index.php?page=despesas&action=lixeira');
    }

    public function hardDelete(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('despesas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        verifyCsrf();
        (new Despesa())->hardDelete((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Despesa eliminada permanentemente.');
        redirect('index.php?page=despesas&action=lixeira');
    }
}
