<?php
// controllers/DashboardController.php

require_once __DIR__ . '/../models/Receita.php';
require_once __DIR__ . '/../models/Despesa.php';
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/permissoes.php';
require_once __DIR__ . '/../middleware/auth.php';

class DashboardController {

    public function index(): void {
        requireLogin();

        $user      = currentUser();
        $escola_id = $user['escola_id'] ?? null;
        $mes       = date('Y-m');

        // Verificar permissões disponíveis
        $podeVerReceitas  = temPermissao('receitas_ver');
        $podeVerDespesas  = temPermissao('despesas_ver');
        $podeVerAlunos    = temPermissao('alunos_ver');
        $podeVerRelatorio = temPermissao('relatorio_ver');

        // Inicializar tudo como null/vazio — só preencher se tiver permissão
        $totalReceitas        = null;
        $totalDespesas        = null;
        $lucro                = null;
        $alunosComDivida      = null;
        $topDevedores         = [];
        $despesasPorCategoria = [];
        $ultimasReceitas      = [];
        $ultimasDespesas      = [];
        $isSuperadminGlobal   = false;
        $totalEscolas         = 0;

        // ── SuperAdmin global (sem escola) ──────────────────────────────
        if ($user['role'] === 'superadmin' && $escola_id === null) {
            $db = getDB();

            $stmt = $db->prepare(
                "SELECT COALESCE(SUM(valor),0) FROM receitas
                 WHERE DATE_FORMAT(data,'%Y-%m')=? AND eliminado=0"
            );
            $stmt->execute([$mes]);
            $totalReceitas = (float)$stmt->fetchColumn();

            $stmt = $db->prepare(
                "SELECT COALESCE(SUM(valor),0) FROM despesas
                 WHERE DATE_FORMAT(data,'%Y-%m')=? AND eliminado=0"
            );
            $stmt->execute([$mes]);
            $totalDespesas = (float)$stmt->fetchColumn();

            $lucro = $totalReceitas - $totalDespesas;

            // Devedores (calculado via receitas)
            $stmt = $db->query(
                "SELECT a.*, e.nome as escola_nome,
                    COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id=a.id AND r.eliminado=0),0) AS pago_total,
                    a.preco_total - COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id=a.id AND r.eliminado=0),0) AS divida
                 FROM alunos a
                 JOIN escolas e ON e.id = a.escola_id
                 HAVING divida > 0
                 ORDER BY divida DESC LIMIT 10"
            );
            $topDevedores    = $stmt->fetchAll();
            $alunosComDivida = count($topDevedores);

            $stmt = $db->prepare(
                "SELECT categoria, SUM(valor) as total
                 FROM despesas WHERE DATE_FORMAT(data,'%Y-%m')=? AND eliminado=0
                 GROUP BY categoria ORDER BY total DESC"
            );
            $stmt->execute([$mes]);
            $despesasPorCategoria = $stmt->fetchAll();

            $stmt = $db->prepare(
                "SELECT r.*, a.nome as aluno_nome, e.nome as escola_nome
                 FROM receitas r
                 LEFT JOIN alunos a ON a.id = r.aluno_id
                 JOIN escolas e ON e.id = r.escola_id
                 WHERE DATE_FORMAT(r.data,'%Y-%m')=? AND r.eliminado=0
                 ORDER BY r.data DESC LIMIT 5"
            );
            $stmt->execute([$mes]);
            $ultimasReceitas = $stmt->fetchAll();

            $stmt = $db->prepare(
                "SELECT d.*, e.nome as escola_nome
                 FROM despesas d
                 JOIN escolas e ON e.id = d.escola_id
                 WHERE DATE_FORMAT(d.data,'%Y-%m')=? AND d.eliminado=0
                 ORDER BY d.data DESC LIMIT 5"
            );
            $stmt->execute([$mes]);
            $ultimasDespesas = $stmt->fetchAll();

            $stmt = $db->query("SELECT COUNT(*) FROM escolas");
            $totalEscolas       = (int)$stmt->fetchColumn();
            $isSuperadminGlobal = true;

            require __DIR__ . '/../views/dashboard/index.php';
            return;
        }

        // ── Escola normal — respeitar permissões ────────────────────────
        $receitaModel = new Receita();
        $despesaModel = new Despesa();
        $alunoModel   = new Aluno();

        // Receitas
        if ($podeVerReceitas) {
            $totalReceitas   = $receitaModel->totalByMonth($escola_id, $mes);
            $ultimasReceitas = array_slice(
                $receitaModel->list($escola_id, ['mes' => $mes]), 0, 5
            );
        }

        // Despesas
        if ($podeVerDespesas) {
            $totalDespesas        = $despesaModel->totalByMonth($escola_id, $mes);
            $despesasPorCategoria = $despesaModel->totalByCategory($escola_id, $mes);
            $ultimasDespesas      = array_slice(
                $despesaModel->list($escola_id, ['mes' => $mes]), 0, 5
            );
        }

        // Lucro só faz sentido se ver ambos
        if ($podeVerReceitas && $podeVerDespesas) {
            $lucro = ($totalReceitas ?? 0) - ($totalDespesas ?? 0);
        }

        // Alunos / devedores
        if ($podeVerAlunos) {
            $alunosComDivida = $alunoModel->countWithDebt($escola_id);
            $topDevedores    = $alunoModel->listWithDebt($escola_id);
        }

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
