<?php
session_start();
require_once '../config/db.php';

define('TEMPO_SESSAO', 15 * 60);
define('BLOQUEIO_INICIAL', 30);

$mensagem = "";
$_SESSION['tentativas'] = $_SESSION['tentativas'] ?? 0;
$_SESSION['bloqueio_expira'] = $_SESSION['bloqueio_expira'] ?? 0;
$_SESSION['ultimo_login_tempo'] = $_SESSION['ultimo_login_tempo'] ?? 0;

$agora = time();

function gerarCaptcha() {
  $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  $texto = '';
  for ($i = 0; $i < 5; $i++) {
    $texto .= $letras[random_int(0, strlen($letras) - 1)];
  }
  $_SESSION['captcha_text'] = $texto;
  return $texto;
}

$mostrarCaptcha = $_SESSION['tentativas'] >= 5;
$captchaTexto = $mostrarCaptcha ? gerarCaptcha() : '';

if ($agora < $_SESSION['bloqueio_expira']) {
  $tempoRestante = $_SESSION['bloqueio_expira'] - $agora;
  $mensagem = "Muitas tentativas. Tente novamente em {$tempoRestante}s.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
  $senha = $_POST['senha'] ?? '';
  $captcha = $_POST['captcha'] ?? '';

  if ($email && $senha) {
    if ($mostrarCaptcha && strtolower($captcha) !== strtolower($_SESSION['captcha_text'] ?? '')) {
      $mensagem = "CAPTCHA incorreto.";
    } else {
      $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($senha, $user['senha'])) {
        if ($user['estado'] === 'inativo') {
          $mensagem = "Este utilizador está inativo.";
        } else {
          session_regenerate_id(true);
          unset($user['senha']);
          $_SESSION['user'] = $user;
          $_SESSION['tentativas'] = 0;
          $_SESSION['ultimo_login_tempo'] = time();
          header("Location: receitas.php");
          exit;
        }
      } else {
        $_SESSION['tentativas']++;
        $mensagem = "Credenciais inválidas.";
        if ($_SESSION['tentativas'] >= 5) {
          $bloqueio = BLOQUEIO_INICIAL * ($_SESSION['tentativas'] - 4);
          $_SESSION['bloqueio_expira'] = time() + $bloqueio;
        }
      }
    }
  } else {
    $mensagem = "Preencha todos os campos.";
  }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Login Seguro</title>
   <link rel="stylesheet" href="./../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./../assets/css/bootstrap-icons.css">
  <link rel="stylesheet" href="./../assets/css/all.min.css"><style>
    body {
      background: linear-gradient(120deg, #054c98, #42566b);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
      background-color: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(10px);
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
      padding: 40px;
      border-radius: 12px;
      color: #fff;
      width: 100%;
      max-width: 520px;
    }
    .form-label {
      font-weight: 500;
    }
    .form-control {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    .form-control::placeholder {
      color: #ccc;
    }
    .btn-custom {
      background-color: #00bfff;
      border: none;
      transition: 0.3s;
    }
    .btn-custom:hover {
      background-color: #0099cc;
    }
    .captcha-box {
      font-size: 24px;
      font-weight: bold;
      background-color: #333;
      color: #00ffcc;
      letter-spacing: 6px;
      text-align: center;
      padding: 10px;
      border-radius: 5px;
      user-select: none;
    }
    .logo {
      text-align: center;
      margin-bottom: 5px;
    }
    .logo img {
      max-width: 240px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo">
      <img src="./../assets/info11.png" alt="Logotipo">
    </div>
    <h4 class="text-center mb-1">Acesso ao Sistema</h4>

    <?php if (!empty($mensagem)): ?>
      <div class="alert alert-danger text-center">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensagem) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
        <input type="email" name="email" class="form-control" placeholder="email@exemplo.com" required>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-lock"></i> Senha</label>
        <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
      </div>

      <?php if ($mostrarCaptcha): ?>
        <div class="mb-3">
          <label class="form-label">CAPTCHA</label>
          <div class="captcha-box mb-2"><?= $captchaTexto ?></div>
          <input type="text" name="captcha" class="form-control" placeholder="Digite o texto acima" required>
        </div>
      <?php endif; ?>

      <div class="d-grid mt-4">
        <button type="submit" name="login" class="btn btn-custom">
          <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
        </button>
      </div>
      <div class="text-center mt-3">
        <small>Sistema de Gestão - Apoio ao cliente (+239 988 35 75)</small>
      </div>
    </form>
  </div>
</body>
</html>
