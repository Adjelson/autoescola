<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Acesso restrito a admin
if ($_SESSION['user']['tipo'] !== 'admin') {
  header("Location: dashboard.php");
  exit;
}

// Criar utilizador
if (isset($_POST['criar'])) {
  $nome = trim($_POST['nome']);
  $email = trim($_POST['email']);
  $tipo = $_POST['tipo'];
  $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
  $estado = 'ativo';

  // Evita duplicados
  $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $check->execute([$email]);
  if ($check->rowCount() > 0) {
    $erro = "Email já existe.";
  } else {
    $stmt = $conn->prepare("INSERT INTO users (nome, email, senha, tipo, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $senha, $tipo, $estado]);
    header("Location: users.php?criado=1");
    exit;
  }
}

// Inativar
if (isset($_GET['inativar'])) {
  $id = intval($_GET['inativar']);
  $stmt = $conn->prepare("UPDATE users SET estado = 'inativo' WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: users.php?inativado=1");
  exit;
}

// Ativar
if (isset($_GET['ativar'])) {
  $id = intval($_GET['ativar']);
  $stmt = $conn->prepare("UPDATE users SET estado = 'ativo' WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: users.php?ativado=1");
  exit;
}

// Editar
if (isset($_POST['editar'])) {
  $id = intval($_POST['id']);
  $nome = trim($_POST['nome']);
  $email = trim($_POST['email']);
  $tipo = $_POST['tipo'];
  $senha = $_POST['nova_senha'];

  if (!empty($senha)) {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET nome = ?, email = ?, tipo = ?, senha = ? WHERE id = ?");
    $stmt->execute([$nome, $email, $tipo, $senhaHash, $id]);
  } else {
    $stmt = $conn->prepare("UPDATE users SET nome = ?, email = ?, tipo = ? WHERE id = ?");
    $stmt->execute([$nome, $email, $tipo, $id]);
  }

  header("Location: users.php?editado=1");
  exit;
}

// Lista
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h3>Gestão de Utilizadores</h3>

<?php if (!empty($erro)): ?>
  <div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<?php if (isset($_GET['criado'])): ?>
  <div class="alert alert-success">Utilizador criado com sucesso!</div>
<?php elseif (isset($_GET['inativado'])): ?>
  <div class="alert alert-warning">Utilizador inativado.</div>
<?php elseif (isset($_GET['ativado'])): ?>
  <div class="alert alert-success">Utilizador reativado.</div>
<?php elseif (isset($_GET['editado'])): ?>
  <div class="alert alert-info">Utilizador editado com sucesso.</div>
<?php endif; ?>

<!-- Botão para abrir modal de criação -->
<div class="mb-3">
  <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCriar">
    <i class="bi bi-plus-circle me-1"></i> Novo Utilizador
  </button>
</div>

<!-- Tabela de usuários -->
<table class="table table-hover mb-0 align-middle">
  <thead class="table-primary">
    <tr>
      <th>Nome</th>
      <th>Email</th>
      <th>Tipo</th>
      <th>Estado</th>
      <th>Registo</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['nome']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge bg-info"><?= $u['tipo'] ?></span></td>
        <td><span class="badge bg-<?= $u['estado'] === 'ativo' ? 'success' : 'danger' ?>"><?= $u['estado'] ?></span></td>
        <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
        <td>
          <?php if ($u['estado'] === 'ativo'): ?>
            <a href="users.php?inativar=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Inativar utilizador?')">
              Inativar
            </a>
          <?php else: ?>
            <a href="users.php?ativar=<?= $u['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Ativar utilizador?')">
              Ativar
            </a>
          <?php endif; ?>

          <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modal<?= $u['id'] ?>">
            <i class="fas fa-edit me-1"></i> Editar
          </button>

          <!-- Modal edição -->
          <div class="modal fade" id="modal<?= $u['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Editar Utilizador</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <div class="mb-3">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="<?= $u['nome'] ?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $u['email'] ?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Tipo</label>
                    <select name="tipo" class="form-select">
                      <option value="secretario" <?= $u['tipo'] === 'secretario' ? 'selected' : '' ?>>Secretário</option>
                      <option value="prof_pratica" <?= $u['tipo'] === 'prof_pratica' ? 'selected' : '' ?>>Prof. Prática</option>
                      <option value="prof_teorica" <?= $u['tipo'] === 'prof_teorica' ? 'selected' : '' ?>>Prof. Teórica</option>
                      <option value="admin" <?= $u['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label>Nova Senha (opcional)</label>
                    <input type="password" name="nova_senha" class="form-control">
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button class="btn btn-primary" name="editar">Salvar</button>
                </div>
              </form>
            </div>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Modal criação -->
<div class="modal fade" id="modalCriar" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Novo Utilizador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Tipo</label>
          <select name="tipo" class="form-select" required>
            <option value="secretario">Secretário</option>
            <option value="prof_pratica">Prof. Prática</option>
            <option value="prof_teorica">Prof. Teórica</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Senha</label>
          <input type="password" name="senha" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" name="criar">Criar</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
