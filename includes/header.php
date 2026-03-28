<?php
if (!isset($_SESSION)) session_start();

// Detecta a página atual
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="./../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./../assets/css/bootstrap-icons.css">
  <link rel="stylesheet" href="./../assets/css/all.min.css">
  
  <style>
    body { font-size: 1.05rem;color: black; }
    .navbar-brand { font-size: 1.5rem; font-weight: bold; color: #fff; }
    .sidebar-heading { font-size: 1.2rem; font-weight: bold; }
    .list-group-item { font-size: 1.1rem; color: white; }
    #sidebar-wrapper { background: #001f3f; color: white; }
    #sidebar-wrapper a { color: #fff; }
    #sidebar-wrapper a:hover { background: #0056b3; }
    .navbar-dark.bg-dark { background-color: #003366 !important; }
    .top-icons .bi { font-size: 1.4rem; color: #fff; margin-left: 20px; cursor: pointer; }
    .top-icons .bi:hover { color: #cce6ff; }
    .active-link {
      background-color: #3399ff !important;
      color: #fff !important;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-tachometer-alt me-2"></i> Painel de controlo
    </a>
    <div class="ms-auto d-flex align-items-center top-icons">
      <span class="text-white me-3">Olá, <strong><?= $_SESSION['user']['nome'] ?? 'N/A' ?></strong></span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm ms-3">
        <i class="fas fa-sign-out-alt"></i> Sair
      </a>
    </div>
  </nav>

  <div class="d-flex">
    <!-- Sidebar -->
    <div class="border-end" id="sidebar-wrapper" style="width: 240px; min-height: 100vh;">
      <div class="list-group list-group-flush">
        <p></p>
        <?php if (isset($_SESSION['user']['tipo']) && $_SESSION['user']['tipo'] === 'admin') : ?>
          <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'dashboard.php' ? 'active-link' : '' ?>">
            <i class="fas fa-chart-line me-2"></i> Dashboard
          </a>
          <a href="users.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'users.php' ? 'active-link' : '' ?>">
            <i class="fas fa-users me-2"></i> Utilizadores
          </a>
        <?php endif; ?>

        <a href="receitas.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'receitas.php' ? 'active-link' : '' ?>">
          <i class="fas fa-arrow-circle-down me-2"></i> Receitas
        </a>
        <a href="despesas.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'despesas.php' ? 'active-link' : '' ?>">
          <i class="fas fa-arrow-circle-up me-2"></i> Despesas
        </a>
        <a href="alunos.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'alunos.php' ? 'active-link' : '' ?>">
          <i class="fas fa-user-graduate me-2"></i> Alunos
        </a>

        <?php if (isset($_SESSION['user']['tipo']) && $_SESSION['user']['tipo'] === 'admin') : ?>
          <a href="transacoes.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'transacoes.php' ? 'active-link' : '' ?>">
            <i class="fas fa-exchange-alt me-2"></i> Transações
          </a>
          <a href="salarios.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'salarios.php' ? 'active-link' : '' ?>">
            <i class="fas fa-money-bill-wave me-2"></i> Salários
          </a>
        <?php endif; ?>

        <a href="pedido_exame.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'pedido_exame.php' ? 'active-link' : '' ?>">
          <i class="fas fa-clipboard-check me-2"></i> Pedido de Exames
        </a>
        <a href="pedido_licenca.php" class="list-group-item list-group-item-action bg-transparent <?= $currentPage == 'pedido_licenca.php' ? 'active-link' : '' ?>">
          <i class="fas fa-id-card-alt me-2"></i> Licenças
        </a>
        <a class="list-group-item list-group-item-action bg-transpare" style="background-color: red;">
          <i class="fas fa-table me-2"></i> Tabela de Exames
        </a>
      </div>
      <div class="sidebar-heading text-center py-4">
        <img src="./../assets/info11.png" alt="logotipo" style="width: 170px; height: auto;">
      </div>
    </div>

    <!-- Conteúdo da página -->
    <div class="container-fluid p-4">
