<?php
// controllers/AlunosController.php

require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../middleware/auth.php';

class AlunosController {

    public function index(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_ver')) { flash('danger','Sem permissão.'); redirect('index.php?page=dashboard'); }
        $escola_id  = escolarId();
        $search     = trim($_GET['q'] ?? '');
        $alunoModel = new Aluno();
        $alunos     = $alunoModel->listBySchool($escola_id, $search);
        require __DIR__ . '/../views/alunos/index.php';
    }

    public function create(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        require __DIR__ . '/../views/alunos/form.php';
    }

    public function store(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_criar')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        verifyCsrf();

        $escola_id = escolarId();
        $nome      = trim($_POST['nome'] ?? '');
        $pacote    = trim($_POST['pacote'] ?? '');
        $preco     = (float)str_replace(',', '.', $_POST['preco_total'] ?? 0);

        if (empty($nome) || empty($pacote) || $preco <= 0) {
            flash('danger', 'Preencha todos os campos corretamente.');
            redirect('index.php?page=alunos&action=create');
        }

        (new Aluno())->create([
            'escola_id'   => $escola_id,
            'nome'        => $nome,
            'pacote'      => $pacote,
            'preco_total' => $preco,
        ]);

        flash('success', 'Aluno criado com sucesso.');
        redirect('index.php?page=alunos');
    }

    public function edit(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_editar')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        $escola_id = escolarId();
        $id        = (int)($_GET['id'] ?? 0);
        $aluno     = (new Aluno())->findById($id, $escola_id);
        if (!$aluno) { flash('danger', 'Aluno não encontrado.'); redirect('index.php?page=alunos'); }
        require __DIR__ . '/../views/alunos/form.php';
    }

    public function update(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_editar')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        verifyCsrf();

        $escola_id = escolarId();
        $id        = (int)($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $pacote    = trim($_POST['pacote'] ?? '');
        $preco     = (float)str_replace(',', '.', $_POST['preco_total'] ?? 0);

        if (empty($nome) || empty($pacote) || $preco <= 0) {
            flash('danger', 'Preencha todos os campos corretamente.');
            redirect('index.php?page=alunos&action=edit&id=' . $id);
        }

        (new Aluno())->update($id, $escola_id, [
            'nome'        => $nome,
            'pacote'      => $pacote,
            'preco_total' => $preco,
        ]);

        flash('success', 'Aluno atualizado.');
        redirect('index.php?page=alunos');
    }

    public function delete(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('alunos_eliminar')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        verifyCsrf();
        (new Aluno())->delete((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Aluno removido.');
        redirect('index.php?page=alunos');
    }
}
