<?php
// controllers/EscolasController.php

require_once __DIR__ . '/../models/Escola.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class EscolasController {

    public function index(): void {
        requireRole('superadmin');

        $db   = getDB();
        $stmt = $db->query(
            "SELECT e.*,
                    COUNT(DISTINCT u.id) as total_utilizadores,
                    COUNT(DISTINCT a.id) as total_alunos
             FROM escolas e
             LEFT JOIN utilizadores u ON u.escola_id = e.id AND u.role != 'superadmin'
             LEFT JOIN alunos a ON a.escola_id = e.id
             GROUP BY e.id
             ORDER BY e.nome"
        );
        $escolas = $stmt->fetchAll();

        require __DIR__ . '/../views/escolas/index.php';
    }

    public function show(): void {
        requireRole('superadmin');

        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM escolas WHERE id = ?");
        $stmt->execute([$id]);
        $escola = $stmt->fetch();

        if (!$escola) {
            flash('danger', 'Escola não encontrada.');
            redirect('index.php?page=escolas');
        }

        // Stats
        $mes = $_GET['mes'] ?? date('Y-m');

        $stmt = $db->prepare("SELECT COALESCE(SUM(valor),0) FROM receitas WHERE escola_id=? AND DATE_FORMAT(data,'%Y-%m')=?");
        $stmt->execute([$id, $mes]);
        $totalReceitas = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas WHERE escola_id=? AND DATE_FORMAT(data,'%Y-%m')=?");
        $stmt->execute([$id, $mes]);
        $totalDespesas = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM alunos WHERE escola_id=?");
        $stmt->execute([$id]);
        $totalAlunos = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM alunos WHERE escola_id=? AND pago_total < preco_total");
        $stmt->execute([$id]);
        $alunosDevedores = (int)$stmt->fetchColumn();

        $stmt = $db->prepare(
            "SELECT * FROM utilizadores WHERE escola_id=? ORDER BY role, nome"
        );
        $stmt->execute([$id]);
        $utilizadores = $stmt->fetchAll();

        require __DIR__ . '/../views/escolas/show.php';
    }

    public function impersonate(): void {
        requireRole('superadmin');
        verifyCsrf();

        $escola_id = (int)($_POST['escola_id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT u.*, e.nome as escola_nome FROM utilizadores u
             LEFT JOIN escolas e ON e.id = u.escola_id
             WHERE u.escola_id = ? AND u.role = 'admin_escola' AND u.ativo = 1
             LIMIT 1"
        );
        $stmt->execute([$escola_id]);
        $admin = $stmt->fetch();

        if (!$admin) {
            flash('danger', 'Nenhum admin ativo encontrado para esta escola.');
            redirect('index.php?page=escolas');
        }

        // Save superadmin session to return later
        $_SESSION['superadmin_backup'] = $_SESSION['user'];
        $_SESSION['user_id'] = $admin['id'];
        // Admin de escola tem acesso total - permissoes vazias (temPermissao devolve true para admin_escola)
        $_SESSION['user']    = [
            'id'          => $admin['id'],
            'nome'        => $admin['nome'],
            'email'       => $admin['email'],
            'role'        => $admin['role'],
            'escola_id'   => $admin['escola_id'],
            'escola_nome' => $admin['escola_nome'],
            'permissoes'  => [],
        ];

        flash('warning', 'A visualizar como: ' . $admin['nome'] . ' (' . $admin['escola_nome'] . ')');
        redirect('index.php?page=dashboard');
    }

    public function stopImpersonate(): void {
        if (!empty($_SESSION['superadmin_backup'])) {
            $backup = $_SESSION['superadmin_backup'];
            $_SESSION['user_id'] = $backup['id'];
            $_SESSION['user']    = $backup;
            unset($_SESSION['superadmin_backup']);
            flash('success', 'Voltou à conta SuperAdmin.');
        }
        redirect('index.php?page=escolas');
    }
}
