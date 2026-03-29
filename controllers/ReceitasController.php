<?php
// controllers/ReceitasController.php

require_once __DIR__ . '/../models/Receita.php';
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../middleware/auth.php';

class ReceitasController {

    public function index(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_ver')) { flash('danger','Sem permissão.'); redirect('index.php?page=dashboard'); }
        $escola_id    = escolarId();
        $mes          = $_GET['mes']    ?? date('Y-m');
        $tipo         = $_GET['tipo']   ?? '';
        $metodo       = $_GET['metodo'] ?? '';
        $valor_min    = $_GET['valor_min'] ?? '';
        $valor_max    = $_GET['valor_max'] ?? '';
        $aluno_id     = $_GET['aluno_id']  ?? '';
        $model        = new Receita();
        $receitas     = $model->list($escola_id, array_filter(compact('mes','tipo','metodo','valor_min','valor_max','aluno_id')));
        $total        = array_sum(array_column($receitas, 'valor'));
        $totalLixeira = $model->countLixeira($escola_id);
        $alunos       = (new Aluno())->listBySchool($escola_id);
        require __DIR__ . '/../views/receitas/index.php';
    }

    public function lixeira(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        $escola_id = escolarId();
        $model     = new Receita();
        $receitas  = $model->list($escola_id, [], true);
        require __DIR__ . '/../views/receitas/lixeira.php';
    }

    public function create(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        $escola_id = escolarId();
        $alunos    = (new Aluno())->listBySchool($escola_id);
        require __DIR__ . '/../views/receitas/form.php';
    }

    public function store(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        verifyCsrf();

        $escola_id = escolarId();
        $aluno_id  = (int)($_POST['aluno_id'] ?? 0);
        $tipo      = $_POST['tipo']   ?? '';
        $valor     = (float)str_replace(',', '.', $_POST['valor'] ?? 0);
        $data      = $_POST['data']   ?? '';
        $metodo    = $_POST['metodo'] ?? 'numerario';
        $descricao = trim($_POST['descricao'] ?? '');

        $tiposValidos   = array_keys(TIPOS_RECEITA);
        $metodosValidos = ['numerario','transferencia','mbway','multibanco'];

        if (!in_array($tipo, $tiposValidos) || $valor <= 0 || empty($data) || !in_array($metodo, $metodosValidos)) {
            flash('danger', 'Dados inválidos. Verifique todos os campos.');
            redirect('index.php?page=receitas&action=create');
        }

        (new Receita())->create([
            'escola_id' => $escola_id,
            'aluno_id'  => $aluno_id ?: null,
            'tipo'      => $tipo,
            'valor'     => $valor,
            'data'      => $data,
            'metodo'    => $metodo,
            'descricao' => $descricao,
        ]);

        flash('success', 'Receita registada com sucesso.');
        redirect('index.php?page=receitas');
    }

    public function delete(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_eliminar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        verifyCsrf();
        $model = new Receita();
        $model->softDelete((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Receita movida para a reciclagem.');
        redirect('index.php?page=receitas');
    }

    public function restore(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        verifyCsrf();
        (new Receita())->restore((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Receita restaurada.');
        redirect('index.php?page=receitas&action=lixeira');
    }

    public function hardDelete(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('receitas_reciclar')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        verifyCsrf();
        (new Receita())->hardDelete((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Receita eliminada permanentemente.');
        redirect('index.php?page=receitas&action=lixeira');
    }
}
