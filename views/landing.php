<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoEscola Financeiro — Gestão financeira para escolas de condução</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= defined('APP_URL') ? APP_URL : '' ?>/public/css/app.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }

        /* NAVBAR */
        .landing-nav {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* HERO */
        .hero-section {
            background: linear-gradient(150deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
            padding: 5rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            width: 700px; height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(22,163,74,.12) 0%, transparent 70%);
            top: -200px; right: -200px;
            pointer-events: none;
        }

        .hero-graphic {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            padding: 2rem;
            position: relative;
        }

        .hero-graphic::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 21px;
            background: linear-gradient(135deg, #16a34a22, #16a34a11);
            z-index: -1;
        }

        .mini-stat {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .mini-stat-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* BENEFITS */
        .benefits-section { padding: 5rem 0; background: white; }

        /* FEATURES SECTION */
        .features-section { padding: 5rem 0; background: #f9fafb; }

        .feature-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* CTA */
        .cta-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #15803d, #16a34a);
            color: white;
        }

        /* FOOTER */
        footer {
            background: #111827;
            color: #9ca3af;
            padding: 2rem 0;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="landing-nav py-3">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none">
                <div style="width:36px;height:36px;background:#16a34a;border-radius:9px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <span class="fw-bold fs-5" style="color:#111827">AutoEscola Financeiro</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=login"
                   class="btn btn-outline-secondary btn-sm px-3">Login</a>
                <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=registo"
                   class="btn btn-primary btn-sm px-3">Criar conta</a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge mb-3">
                    <i class="bi bi-stars"></i> Gestão 100% digital
                </div>
                <h1 class="hero-title">
                    Controle financeiro<br>
                    <span>completo para<br>escolas de condução</span>
                </h1>
                <p class="text-muted fs-5 mb-4" style="max-width:480px;line-height:1.7">
                    Gerir receitas, despesas, alunos e dívidas nunca foi tão simples.
                    Tudo numa plataforma moderna, rápida e segura.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=registo"
                       class="btn btn-primary btn-lg px-5 py-3 fw-semibold shadow-sm">
                        <i class="bi bi-rocket-takeoff me-2"></i>Começar gratuitamente
                    </a>
                    <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=login"
                       class="btn btn-outline-secondary btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                    </a>
                </div>
                <p class="text-muted small mt-3">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Dados seguros &nbsp;·&nbsp;
                    <i class="bi bi-cloud-check text-success me-1"></i>
                    Na nuvem &nbsp;·&nbsp;
                    <i class="bi bi-phone text-success me-1"></i>
                    Qualquer dispositivo
                </p>
            </div>
            <div class="col-lg-6">
                <div class="hero-graphic">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width:10px;height:10px;border-radius:50%;background:#ef4444"></div>
                        <div style="width:10px;height:10px;border-radius:50%;background:#f59e0b"></div>
                        <div style="width:10px;height:10px;border-radius:50%;background:#22c55e"></div>
                        <span class="text-muted small ms-2">Dashboard — Junho 2025</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background:#dcfce7;color:#16a34a">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:.95rem">12.450 €</div>
                                    <div class="text-muted" style="font-size:.72rem">Receitas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background:#fee2e2;color:#ef4444">
                                    <i class="bi bi-arrow-down-circle-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:.95rem">3.280 €</div>
                                    <div class="text-muted" style="font-size:.72rem">Despesas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background:#dbeafe;color:#3b82f6">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-success" style="font-size:.95rem">9.170 €</div>
                                    <div class="text-muted" style="font-size:.72rem">Lucro</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background:#fef3c7;color:#f59e0b">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:.95rem">48 alunos</div>
                                    <div class="text-muted" style="font-size:.72rem">Ativos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Mini table preview -->
                    <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
                        <div style="background:#f9fafb;padding:.5rem 1rem;font-size:.75rem;font-weight:600;color:#6b7280;border-bottom:1px solid #e5e7eb">
                            ÚLTIMAS RECEITAS
                        </div>
                        <?php
                        $rows = [
                            ['João Silva', 'Aulas', '350 €', '#dcfce7', '#16a34a'],
                            ['Maria Costa', 'Inscrição', '80 €', '#dbeafe', '#3b82f6'],
                            ['Pedro Nunes', 'Exame', '120 €', '#ede9fe', '#7c3aed'],
                        ];
                        foreach ($rows as $r):
                        ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 1rem;font-size:.8rem;border-bottom:1px solid #f3f4f6">
                            <span class="fw-medium"><?= $r[0] ?></span>
                            <span style="background:<?= $r[3] ?>;color:<?= $r[4] ?>;padding:.15em .6em;border-radius:20px;font-size:.7rem;font-weight:600"><?= $r[1] ?></span>
                            <span class="fw-semibold" style="color:#16a34a"><?= $r[2] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BENEFITS -->
<section class="benefits-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="hero-badge d-inline-flex mb-3">
                <i class="bi bi-check2-circle me-1"></i> Porquê escolher-nos?
            </span>
            <h2 class="fw-bold" style="font-size:2rem">Tudo o que a sua escola precisa</h2>
            <p class="text-muted">Uma plataforma completa, pensada para escolas de condução portuguesas</p>
        </div>
        <div class="row g-4">
            <?php
            $benefits = [
                ['bi-cloud-check-fill', 'Dados na nuvem', 'Aceda aos seus dados a partir de qualquer lugar, a qualquer hora, em qualquer dispositivo.'],
                ['bi-bar-chart-line-fill', 'Dashboard intuitivo', 'Visão completa das receitas, despesas e lucro do mês em segundos.'],
                ['bi-people-fill', 'Gestão de Alunos', 'Controle pacotes, valores pagos e dívidas de cada aluno de forma clara.'],
                ['bi-shield-lock-fill', '100% Seguro', 'Passwords com encriptação bcrypt, sessões protegidas e multi-tenant.'],
                ['bi-person-fill-gear', 'Multi-utilizador', 'Crie funcionários com permissões limitadas e mantenha o controlo.'],
                ['bi-phone-fill', 'Responsivo', 'Interface moderna que funciona no telemóvel, tablet e computador.'],
            ];
            foreach ($benefits as $b):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="bi <?= $b[0] ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= $b[1] ?></h5>
                    <p class="text-muted small mb-0"><?= $b[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge d-inline-flex mb-3">
                    <i class="bi bi-list-check me-1"></i> Funcionalidades
                </span>
                <h2 class="fw-bold mb-4" style="font-size:1.9rem">
                    Controlo total das<br>finanças da sua escola
                </h2>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Registo de Receitas</h6>
                        <p class="text-muted small mb-0">Inscrições, aulas, exames. Associe pagamentos a alunos e o sistema atualiza automaticamente as dívidas.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Controlo de Despesas</h6>
                        <p class="text-muted small mb-0">Combustível, manutenção, salários, renda. Filtre por categoria e mês.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Gestão de Alunos</h6>
                        <p class="text-muted small mb-0">Veja quem tem dívidas em aberto, o pacote contratado e o histórico de pagamentos.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-buildings-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Multi-escola (SaaS)</h6>
                        <p class="text-muted small mb-0">Cada escola vê apenas os seus dados. Segurança e privacidade garantidas.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white rounded-4 p-4 shadow-sm border">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-grid-1x2-fill text-primary me-2"></i>Resumo do Mês
                    </h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Receitas</span>
                            <span class="small fw-semibold text-success">12.450 €</span>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px">
                            <div class="progress-bar bg-success" style="width:80%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Despesas</span>
                            <span class="small fw-semibold text-danger">3.280 €</span>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px">
                            <div class="progress-bar bg-danger" style="width:26%"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Lucro do mês</span>
                        <span class="fw-bold fs-5 text-success">9.170 €</span>
                    </div>
                    <hr>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="small text-muted">Alunos</div>
                            <div class="fw-bold">48</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">Com dívida</div>
                            <div class="fw-bold text-warning">7</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">Total dívidas</div>
                            <div class="fw-bold text-danger">1.840 €</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container text-center">
        <i class="bi bi-car-front-fill fs-1 mb-3 d-block opacity-75"></i>
        <h2 class="fw-bold mb-3" style="font-size:2rem">Pronto para começar?</h2>
        <p class="mb-4 opacity-75 fs-5">Registe a sua escola em menos de 2 minutos. Sem compromissos.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=registo"
               class="btn btn-light btn-lg px-5 py-3 fw-bold text-success shadow">
                <i class="bi bi-rocket-takeoff me-2"></i>Criar conta grátis
            </a>
            <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/index.php?page=login"
               class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:28px;height:28px;background:#16a34a;border-radius:7px;display:flex;align-items:center;justify-content:center;color:white;font-size:.85rem">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <span class="fw-semibold text-white">AutoEscola Financeiro</span>
                </div>
                <p class="small mb-0">Gestão financeira para escolas de condução</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <small>&copy; <?= date('Y') ?> AutoEscola Financeiro. Todos os direitos reservados.</small>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
