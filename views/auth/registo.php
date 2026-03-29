<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Escola — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width:540px">
        <div class="auth-logo">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <h1 class="text-center fw-bold fs-4 mb-1">Criar Conta</h1>
        <p class="text-center text-muted small mb-4">Registe a sua escola de condução</p>

        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['registo_errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($_SESSION['registo_errors'] as $err): unset($_SESSION['registo_errors']); ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php
        $old = $_SESSION['registo_old'] ?? [];
        unset($_SESSION['registo_old']);
        ?>

        <form method="POST" action="<?= APP_URL ?>/index.php?page=registo" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em">
                <i class="bi bi-building me-1"></i>Dados da Escola
            </p>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="escola_nome">Nome da Escola *</label>
                    <input type="text" class="form-control" id="escola_nome" name="escola_nome"
                           value="<?= e($old['escola_nome'] ?? '') ?>" placeholder="AutoEscola Exemplo" required>
                </div>
                <div class="col-sm-5">
                    <label class="form-label" for="escola_nif">NIF *</label>
                    <input type="text" class="form-control" id="escola_nif" name="escola_nif"
                           value="<?= e($old['escola_nif'] ?? '') ?>" placeholder="500000000"
                           maxlength="9" pattern="\d{9}" required>
                </div>
                <div class="col-sm-7">
                    <label class="form-label" for="escola_email">Email da Escola *</label>
                    <input type="email" class="form-control" id="escola_email" name="escola_email"
                           value="<?= e($old['escola_email'] ?? '') ?>" placeholder="escola@email.pt" required>
                </div>
            </div>

            <hr class="my-3">
            <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em">
                <i class="bi bi-person me-1"></i>Dados do Administrador
            </p>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label" for="admin_nome">Nome *</label>
                    <input type="text" class="form-control" id="admin_nome" name="admin_nome"
                           value="<?= e($old['admin_nome'] ?? '') ?>" placeholder="João Silva" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="admin_email">Email *</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email"
                           value="<?= e($old['admin_email'] ?? '') ?>" placeholder="admin@email.pt" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Mín. 8 caracteres" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="password2">Confirmar Password *</label>
                    <input type="password" class="form-control" id="password2" name="password2"
                           placeholder="Repetir password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-1">
                <i class="bi bi-check-circle me-2"></i>Criar Conta
            </button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Já tem conta? <a href="<?= APP_URL ?>/index.php?page=login" class="text-primary fw-semibold">Fazer login</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
