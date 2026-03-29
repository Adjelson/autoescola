<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Escola.php';
require_once __DIR__ . '/../config/app.php';

class AuthController {

    public function showLogin(): void {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void {
        verifyCsrf();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('danger', 'Preencha todos os campos.');
            redirect('index.php?page=login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            flash('danger', 'Email ou password incorretos.');
            redirect('index.php?page=login');
        }

        // Normalizar permissões (sempre array)
        $permissoes = $user['permissoes'] ?? [];
        if (is_string($permissoes)) {
            $permissoes = json_decode($permissoes, true) ?? [];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = [
            'id'          => $user['id'],
            'nome'        => $user['nome'],
            'email'       => $user['email'],
            'role'        => $user['role'],
            'escola_id'   => $user['escola_id'],
            'escola_nome' => $user['escola_nome'] ?? '',
            'permissoes'  => $permissoes,
        ];

        session_regenerate_id(true);
        flash('success', 'Bem-vindo, ' . e($user['nome']) . '!');
        redirect('index.php?page=dashboard');
    }

    public function showRegisto(): void {
        require __DIR__ . '/../views/auth/registo.php';
    }

    public function registo(): void {
        verifyCsrf();
        $errors = [];

        $escola_nome  = trim($_POST['escola_nome'] ?? '');
        $escola_nif   = trim($_POST['escola_nif'] ?? '');
        $escola_email = trim($_POST['escola_email'] ?? '');
        $admin_nome   = trim($_POST['admin_nome'] ?? '');
        $admin_email  = trim($_POST['admin_email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $password2    = $_POST['password2'] ?? '';

        if (empty($escola_nome)) $errors[] = 'Nome da escola é obrigatório.';
        if (empty($escola_nif) || !preg_match('/^\d{9}$/', $escola_nif)) $errors[] = 'NIF inválido (9 dígitos).';
        if (empty($escola_email) || !filter_var($escola_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email da escola inválido.';
        if (empty($admin_nome)) $errors[] = 'Nome do administrador é obrigatório.';
        if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email do administrador inválido.';
        if (strlen($password) < 8) $errors[] = 'Password deve ter pelo menos 8 caracteres.';
        if ($password !== $password2) $errors[] = 'As passwords não coincidem.';

        $escolaModel = new Escola();
        $userModel   = new User();

        if (empty($errors)) {
            if ($escolaModel->nifExists($escola_nif)) $errors[] = 'Este NIF já está registado.';
            if ($escolaModel->emailExists($escola_email)) $errors[] = 'Este email de escola já está registado.';
            if ($userModel->emailExists($admin_email)) $errors[] = 'Este email de administrador já está em uso.';
        }

        if (!empty($errors)) {
            $_SESSION['registo_errors'] = $errors;
            $_SESSION['registo_old']    = $_POST;
            redirect('index.php?page=registo');
        }

        try {
            $escola_id = $escolaModel->create([
                'nome'  => $escola_nome,
                'nif'   => $escola_nif,
                'email' => $escola_email,
            ]);

            $userModel->create([
                'nome'      => $admin_nome,
                'email'     => $admin_email,
                'password'  => $password,
                'role'      => 'admin_escola',
                'escola_id' => $escola_id,
            ]);

            flash('success', 'Escola registada com sucesso! Faça login para continuar.');
            redirect('index.php?page=login');
        } catch (Exception $e) {
            flash('danger', 'Erro ao criar conta. Tente novamente.');
            redirect('index.php?page=registo');
        }
    }

    public function logout(): void {
        session_destroy();
        redirect('index.php');
    }
}
