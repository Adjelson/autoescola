<?php
// controllers/UtilizadoresController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/permissoes.php';
require_once __DIR__ . '/../middleware/auth.php';

class UtilizadoresController {

    public function index(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        $escola_id    = escolarId();
        $utilizadores = (new User())->listBySchool($escola_id);
        require __DIR__ . '/../views/utilizadores/index.php';
    }

    public function create(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        require __DIR__ . '/../views/utilizadores/form.php';
    }

    public function store(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        verifyCsrf();

        $escola_id = escolarId();
        $nome      = trim($_POST['nome']  ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password']   ?? '';
        $role      = $_POST['role']       ?? 'funcionario';
        $perms     = $_POST['permissoes'] ?? [];

        $errors = [];
        if (empty($nome)) $errors[] = 'Nome é obrigatório.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
        if (strlen($password) < 8) $errors[] = 'Password mínimo 8 caracteres.';
        if (!in_array($role, ['funcionario','admin_escola'])) $errors[] = 'Role inválido.';

        $userModel = new User();
        if ($userModel->emailExists($email)) $errors[] = 'Email já em uso.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old']    = $_POST;
            redirect('index.php?page=utilizadores&action=create');
        }

        // Apenas funcionário tem permissões personalizadas
        $permissoes = null;
        if ($role === 'funcionario') {
            $permissoes = array_values(array_filter(
                array_keys(TODAS_PERMISSOES),
                fn($k) => in_array($k, (array)$perms, true)
            ));
            // Se nenhuma selecionada, usar defaults
            if (empty($permissoes)) {
                $permissoes = PERMISSOES_PADRAO_FUNCIONARIO;
            }
        }

        $userModel->create([
            'nome'       => $nome,
            'email'      => $email,
            'password'   => $password,
            'role'       => $role,
            'escola_id'  => $escola_id,
            'permissoes' => $permissoes,
        ]);

        flash('success', 'Utilizador criado com sucesso.');
        redirect('index.php?page=utilizadores');
    }

    public function editPermissoes(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        $escola_id = escolarId();
        $id        = (int)($_GET['id'] ?? 0);
        $userModel = new User();
        $utilizador = $userModel->findById($id);
        if (!$utilizador || $utilizador['escola_id'] != $escola_id || $utilizador['role'] !== 'funcionario') {
            flash('danger', 'Utilizador não encontrado ou sem permissões personalizáveis.');
            redirect('index.php?page=utilizadores');
        }
        require __DIR__ . '/../views/utilizadores/permissoes.php';
    }

    public function savePermissoes(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        verifyCsrf();
        $escola_id  = escolarId();
        $id         = (int)($_POST['id'] ?? 0);
        $perms      = $_POST['permissoes'] ?? [];
        $permitidas = array_values(array_filter(
            array_keys(TODAS_PERMISSOES),
            fn($k) => in_array($k, (array)$perms, true)
        ));
        (new User())->updatePermissoes($id, $escola_id, $permitidas);
        
        // Atualizar sessão se for o próprio utilizador (edge case)
        if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $id) {
            $_SESSION['user']['permissoes'] = $permitidas;
        }
        flash('success', 'Permissões atualizadas.');
        redirect('index.php?page=utilizadores');
    }

    public function toggleAtivo(): void {
        requireLogin();
        requireEscolaContext();
        requireRole('admin_escola','superadmin');
        verifyCsrf();
        (new User())->toggleAtivo((int)($_POST['id'] ?? 0), escolarId());
        flash('success', 'Estado do utilizador atualizado.');
        redirect('index.php?page=utilizadores');
    }
}
