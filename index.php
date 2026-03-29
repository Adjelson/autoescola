<?php
// index.php — Router principal v2.0

declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/permissoes.php';

// Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/AlunosController.php';
require_once __DIR__ . '/controllers/ReceitasController.php';
require_once __DIR__ . '/controllers/DespesasController.php';
require_once __DIR__ . '/controllers/UtilizadoresController.php';
require_once __DIR__ . '/controllers/PerfilController.php';
require_once __DIR__ . '/controllers/RelatorioController.php';
require_once __DIR__ . '/controllers/ExportController.php';
require_once __DIR__ . '/controllers/EscolasController.php';

$page   = $_GET['page']   ?? null;
$action = $_GET['action'] ?? 'index';
$method = $_SERVER['REQUEST_METHOD'];

// Landing / home
if ($page === null || $page === 'home') {
    if (isLoggedIn()) redirect('index.php?page=dashboard');
    require __DIR__ . '/views/landing.php';
    exit;
}

// Auth (públicas)
if ($page === 'login') {
    $ctrl = new AuthController();
    $method === 'POST' ? $ctrl->login() : (isLoggedIn() ? redirect('index.php?page=dashboard') : $ctrl->showLogin());
    exit;
}
if ($page === 'registo') {
    $ctrl = new AuthController();
    $method === 'POST' ? $ctrl->registo() : (isLoggedIn() ? redirect('index.php?page=dashboard') : $ctrl->showRegisto());
    exit;
}
if ($page === 'logout') {
    (new AuthController())->logout();
    exit;
}

// Rotas protegidas
require_once __DIR__ . '/middleware/auth.php';
requireLogin();

switch ($page) {

    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'alunos':
        $ctrl = new AlunosController();
        match(true) {
            $action === 'create'                     => $ctrl->create(),
            $action === 'store'  && $method === 'POST' => $ctrl->store(),
            $action === 'edit'                       => $ctrl->edit(),
            $action === 'update' && $method === 'POST' => $ctrl->update(),
            $action === 'delete' && $method === 'POST' => $ctrl->delete(),
            default                                  => $ctrl->index(),
        };
        break;

    case 'receitas':
        $ctrl = new ReceitasController();
        match(true) {
            $action === 'lixeira'                           => $ctrl->lixeira(),
            $action === 'create'                            => $ctrl->create(),
            $action === 'store'      && $method === 'POST'  => $ctrl->store(),
            $action === 'delete'     && $method === 'POST'  => $ctrl->delete(),
            $action === 'restore'    && $method === 'POST'  => $ctrl->restore(),
            $action === 'hardDelete' && $method === 'POST'  => $ctrl->hardDelete(),
            default                                         => $ctrl->index(),
        };
        break;

    case 'despesas':
        $ctrl = new DespesasController();
        match(true) {
            $action === 'lixeira'                           => $ctrl->lixeira(),
            $action === 'create'                            => $ctrl->create(),
            $action === 'store'      && $method === 'POST'  => $ctrl->store(),
            $action === 'edit'                              => $ctrl->edit(),
            $action === 'update'     && $method === 'POST'  => $ctrl->update(),
            $action === 'delete'     && $method === 'POST'  => $ctrl->delete(),
            $action === 'restore'    && $method === 'POST'  => $ctrl->restore(),
            $action === 'hardDelete' && $method === 'POST'  => $ctrl->hardDelete(),
            default                                         => $ctrl->index(),
        };
        break;

    case 'utilizadores':
        $ctrl = new UtilizadoresController();
        match(true) {
            $action === 'create'                           => $ctrl->create(),
            $action === 'store'           && $method === 'POST' => $ctrl->store(),
            $action === 'editPermissoes'                   => $ctrl->editPermissoes(),
            $action === 'savePermissoes'  && $method === 'POST' => $ctrl->savePermissoes(),
            $action === 'toggleAtivo'     && $method === 'POST' => $ctrl->toggleAtivo(),
            default                                        => $ctrl->index(),
        };
        break;

    case 'perfil':
        $ctrl = new PerfilController();
        $action === 'updatePassword' && $method === 'POST'
            ? $ctrl->updatePassword()
            : $ctrl->show();
        break;

    case 'relatorio':
        (new RelatorioController())->mensal();
        break;

    case 'export':
        $ctrl = new ExportController();
        $tipo = $_GET['tipo'] ?? '';
        $fmt  = $_GET['fmt']  ?? 'excel';
        match($tipo) {
            'receitas' => $fmt === 'pdf' ? $ctrl->receitasPdf() : $ctrl->receitasExcel(),
            'despesas' => $fmt === 'pdf' ? $ctrl->despesasPdf() : $ctrl->despesasExcel(),
            'alunos'   => $fmt === 'pdf' ? $ctrl->alunosPdf()   : $ctrl->alunosExcel(),
            default    => redirect('index.php?page=dashboard'),
        };
        break;

    case 'escolas':
        $ctrl = new EscolasController();
        match(true) {
            $action === 'show'                               => $ctrl->show(),
            $action === 'impersonate'    && $method === 'POST' => $ctrl->impersonate(),
            $action === 'stopImpersonate'                    => $ctrl->stopImpersonate(),
            default                                          => $ctrl->index(),
        };
        break;

    default:
        http_response_code(404);
        flash('warning', 'Página não encontrada.');
        redirect('index.php?page=dashboard');
}
