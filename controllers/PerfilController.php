<?php
// controllers/PerfilController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';

class PerfilController {

    public function show(): void {
        requireLogin();
        $userModel = new User();
        $user = $userModel->findById((int)currentUser()['id']);
        require __DIR__ . '/../views/perfil/show.php';
    }

    public function updatePassword(): void {
        requireLogin();
        verifyCsrf();

        $atual   = $_POST['password_atual'] ?? '';
        $nova    = $_POST['password_nova'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        $userModel = new User();
        $user = $userModel->findById((int)currentUser()['id']);

        if (!password_verify($atual, $user['password'])) {
            flash('danger', 'Password atual incorreta.');
            redirect('index.php?page=perfil');
        }
        if (strlen($nova) < 8) {
            flash('danger', 'Nova password deve ter pelo menos 8 caracteres.');
            redirect('index.php?page=perfil');
        }
        if ($nova !== $confirm) {
            flash('danger', 'As passwords não coincidem.');
            redirect('index.php?page=perfil');
        }

        $userModel->updatePassword((int)$user['id'], $nova);
        flash('success', 'Password alterada com sucesso.');
        redirect('index.php?page=perfil');
    }
}
