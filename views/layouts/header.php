<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/app.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/index.php?page=dashboard">
            <span class="brand-icon"><i class="bi bi-car-front-fill"></i></span>
            <span class="fw-bold"><?= APP_NAME ?></span>
        </a>
        <button class="btn btn-sm btn-outline-light me-2 d-lg-none" id="sidebarToggle" type="button">
            <i class="bi bi-layout-sidebar"></i>
        </button>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item">
                    <span class="nav-text small text-white-50">
                        <?= e(currentUser()['escola_nome'] ?? 'SuperAdmin') ?>
                    </span>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <div class="avatar-circle">
                            <?= strtoupper(substr(currentUser()['nome'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="d-none d-lg-inline"><?= e(currentUser()['nome'] ?? '') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <span class="dropdown-item-text small text-muted">
                                <?php
                                $roleLabel = [
                                    'superadmin'   => 'Super Administrador',
                                    'admin_escola' => 'Administrador',
                                    'funcionario'  => 'Funcionário',
                                ];
                                echo $roleLabel[currentUser()['role'] ?? ''] ?? currentUser()['role'] ?? '';
                                ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= APP_URL ?>/index.php?page=perfil">
                                <i class="bi bi-person-circle me-2"></i>Meu Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?= APP_URL ?>/index.php?page=logout">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="wrapper">
<!-- SIDEBAR — links controlados por permissão -->
<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav flex-column">

            <!-- Dashboard: sempre visível -->
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/index.php?page=dashboard">
                    <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
                </a>
            </li>

            <?php
            // Construir secção GESTÃO apenas com os links que o utilizador pode ver
            $gestaoLinks = [];

            if (temPermissao('alunos_ver')) {
                $gestaoLinks[] = ['alunos', 'bi-people-fill', 'Alunos'];
            }
            if (temPermissao('receitas_ver')) {
                $gestaoLinks[] = ['receitas', 'bi-arrow-up-circle-fill', 'Receitas'];
            }
            if (temPermissao('despesas_ver')) {
                $gestaoLinks[] = ['despesas', 'bi-arrow-down-circle-fill', 'Despesas'];
            }
            ?>

            <?php if (!empty($gestaoLinks)): ?>
            <li class="nav-section-title">GESTÃO</li>
            <?php foreach ($gestaoLinks as [$pg, $icon, $label]): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === $pg ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/index.php?page=<?= $pg ?>">
                    <i class="bi <?= $icon ?>"></i><span><?= $label ?></span>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php
            // Secção ADMINISTRAÇÃO
            $adminLinks = [];
            if (temPermissao('relatorio_ver')) {
                $adminLinks[] = ['relatorio', 'bi-file-earmark-bar-graph-fill', 'Relatório'];
            }
            if (hasRole('admin_escola', 'superadmin')) {
                $adminLinks[] = ['utilizadores', 'bi-person-fill-gear', 'Utilizadores'];
            }
            ?>

            <?php if (!empty($adminLinks)): ?>
            <li class="nav-section-title">ADMINISTRAÇÃO</li>
            <?php foreach ($adminLinks as [$pg, $icon, $label]): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === $pg ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/index.php?page=<?= $pg ?>">
                    <i class="bi <?= $icon ?>"></i><span><?= $label ?></span>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (hasRole('superadmin')): ?>
            <li class="nav-section-title">SUPERADMIN</li>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'escolas' ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/index.php?page=escolas">
                    <i class="bi bi-buildings-fill"></i><span>Escolas</span>
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </nav>

    <!-- Info do utilizador no fundo da sidebar -->
    <div class="sidebar-footer">
        <?php $role = currentUser()['role'] ?? ''; ?>
        <?php if ($role === 'funcionario'): ?>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-success"></i>
            <span class="small text-muted">
                <?= count(currentUser()['permissoes'] ?? []) ?> permissões ativas
            </span>
        </div>
        <?php endif; ?>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="content-area">
    <?php if (!empty($_SESSION['superadmin_backup'])): ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between py-2 mb-3">
        <span class="small">
            <i class="bi bi-eye-fill me-2"></i>
            A visualizar como <strong><?= e(currentUser()['nome']) ?></strong>
            — <?= e(currentUser()['escola_nome']) ?>
        </span>
        <form method="POST" action="<?= APP_URL ?>/index.php?page=escolas&action=stopImpersonate" class="mb-0">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <button class="btn btn-warning btn-sm py-0 px-2">
                <i class="bi bi-x-circle me-1"></i>Sair
            </button>
        </form>
    </div>
    <?php endif; ?>
