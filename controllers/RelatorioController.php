<?php
// controllers/RelatorioController.php

require_once __DIR__ . '/../models/Receita.php';
require_once __DIR__ . '/../models/Despesa.php';
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/permissoes.php';
require_once __DIR__ . '/../middleware/auth.php';

class RelatorioController {

    public function mensal(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('relatorio_ver')) { flash('danger','Sem permissão.'); redirect('index.php?page=dashboard'); }

        $escola_id    = escolarId();

        // Filtros
        $mes       = $_GET['mes']       ?? date('Y-m');
        $tipo      = $_GET['tipo']      ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $metodo    = $_GET['metodo']    ?? '';

        $receitaModel = new Receita();
        $despesaModel = new Despesa();
        $alunoModel   = new Aluno();

        // Totais do mês (sem outros filtros)
        $totalReceitas = $receitaModel->totalByMonth($escola_id, $mes);
        $totalDespesas = $despesaModel->totalByMonth($escola_id, $mes);
        $lucro         = $totalReceitas - $totalDespesas;

        // Receitas com filtros adicionais
        $filtrosR = array_filter(['mes'=>$mes,'tipo'=>$tipo,'metodo'=>$metodo]);
        $receitas = $receitaModel->list($escola_id, $filtrosR);

        // Despesas com filtros adicionais
        $filtrosD = array_filter(['mes'=>$mes,'categoria'=>$categoria]);
        $despesas = $despesaModel->list($escola_id, $filtrosD);

        // Breakdown
        $receitasPorTipo     = $receitaModel->totalByTipo($escola_id, $mes);
        $despesasPorCategoria = $despesaModel->totalByCategory($escola_id, $mes);

        // Devedores
        $devedores  = $alunoModel->listWithDebt($escola_id);
        $totalDivida = array_sum(array_column($devedores, 'divida'));

        // Meses disponíveis (últimos 24)
        $meses = [];
        for ($i = 0; $i < 24; $i++) {
            $meses[] = date('Y-m', strtotime("-$i months"));
        }

        require __DIR__ . '/../views/relatorio/mensal.php';
    }
}
