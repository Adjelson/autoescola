<?php
$pageTitle  = 'Meu Perfil';
$activePage = 'perfil';
require __DIR__ . '/../layouts/header.php';
?>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-circle me-2 text-primary"></i>Meu Perfil</h1>
        <p class="page-subtitle">Informações da conta e segurança</p>
    </div>
</div>

<div class="row g-4">

    <!-- INFO CARD -->
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div style="width:80px;height:80px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;margin:0 auto 1rem">
                    <?= strtoupper(substr($user['nome'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= e($user['nome']) ?></h5>
                <p class="text-muted small mb-2"><?= e($user['email']) ?></p>
                <?php
                $roleBadge = [
                    'superadmin'   => ['SuperAdmin','#7c3aed','#ede9fe'],
                    'admin_escola' => ['Admin Escola','#0284c7','#dbeafe'],
                    'funcionario'  => ['Funcionário','#16a34a','#dcfce7'],
                ];
                $rb = $roleBadge[$user['role']] ?? [$user['role'],'#6b7280','#f3f4f6'];
                ?>
                <span class="badge badge-tipo" style="background:<?= $rb[2] ?>;color:<?= $rb[1] ?>">
                    <?= $rb[0] ?>
                </span>
                <?php if ($user['escola_nome']): ?>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-building me-1"></i><?= e($user['escola_nome']) ?>
                </p>
                <?php endif; ?>
                <p class="text-muted small mt-1 mb-0">
                    <i class="bi bi-calendar3 me-1"></i>
                    Membro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- CHANGE PASSWORD -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-lock-fill me-2 text-warning"></i>Alterar Password</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/index.php?page=perfil&action=updatePassword" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label">Password atual *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="password_atual"
                                   placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label">Nova password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" name="password_nova"
                                       placeholder="Mín. 8 caracteres" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Confirmar nova *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control" name="password_confirm"
                                       placeholder="Repetir password" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning fw-semibold">
                        <i class="bi bi-shield-check me-2"></i>Alterar Password
                    </button>
                </form>
            </div>
        </div>

        <!-- SECURITY INFO -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-shield-fill-check me-2 text-success"></i>Segurança da Conta</h6>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-3 rounded" style="background:#f0fdf4">
                            <i class="bi bi-lock-fill text-success fs-4 d-block mb-1"></i>
                            <small class="text-muted">Password encriptada<br>com bcrypt</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded" style="background:#eff6ff">
                            <i class="bi bi-person-badge-fill text-primary fs-4 d-block mb-1"></i>
                            <small class="text-muted">Sessão protegida<br>por token CSRF</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded" style="background:#fefce8">
                            <i class="bi bi-building-lock text-warning fs-4 d-block mb-1"></i>
                            <small class="text-muted">Dados isolados<br>por escola</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
