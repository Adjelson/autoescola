<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$categorias = ['A', 'B', 'C', 'D', 'B+A', 'C+A', 'D+A'];
$statuses = ['Em curso', 'Finalizado', 'Exame'];
$sexos = ['Masculino', 'Feminino'];

$statusFiltro = $_GET['status'] ?? '';
$categoriaFiltro = $_GET['categoria'] ?? '';
$nomeFiltro = $_GET['nome'] ?? '';

$erro = '';
$limite = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $limite;

if (isset($_POST['salvar'])) {
  $nome = trim($_POST['nome']);
  $tel = trim($_POST['telefone']);
  $cat = $_POST['categoria'];
  $bi = trim($_POST['bi']);
  $sexo = $_POST['sexo'];
  if ($nome && $cat && $bi && in_array($cat, $categorias) && in_array($sexo, $sexos)) {
    $stmt = $conn->prepare("INSERT INTO alunos (nome, telefone, categoria, bi, sexo, status) VALUES (?, ?, ?, ?, ?, 'Em curso')");
    $stmt->execute([$nome, $tel, $cat, $bi, $sexo]);
    header("Location: alunos.php");
    exit;
  } else {
    $erro = "Preencha todos os campos obrigatórios.";
  }
}

if (isset($_POST['editar'])) {
  $id = intval($_POST['id']);
  $nome = trim($_POST['nome']);
  $tel = trim($_POST['telefone']);
  $cat = $_POST['categoria'];
  $status = $_POST['status'];
  $bi = trim($_POST['bi']);
  $sexo = $_POST['sexo'];
  if ($nome && $cat && $status && $bi && in_array($cat, $categorias) && in_array($status, $statuses) && in_array($sexo, $sexos)) {
    $stmt = $conn->prepare("UPDATE alunos SET nome=?, telefone=?, categoria=?, status=?, bi=?, sexo=? WHERE id=?");
    $stmt->execute([$nome, $tel, $cat, $status, $bi, $sexo, $id]);
    header("Location: alunos.php");
    exit;
  }
}

if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM alunos WHERE id=?");
  $stmt->execute([intval($_GET['delete'])]);
  header("Location: alunos.php");
  exit;
}

$where = "WHERE 1=1";
$params = [];
if ($statusFiltro && in_array($statusFiltro, $statuses)) {
  $where .= " AND status=?";
  $params[] = $statusFiltro;
}
if ($categoriaFiltro && in_array($categoriaFiltro, $categorias)) {
  $where .= " AND categoria=?";
  $params[] = $categoriaFiltro;
}
if ($nomeFiltro) {
  $where .= " AND nome LIKE ?";
  $params[] = "%$nomeFiltro%";
}

$countStmt = $conn->prepare("SELECT COUNT(*) FROM alunos $where");
$countStmt->execute($params);
$totalAlunos = $countStmt->fetchColumn();
$totalPaginas = ceil($totalAlunos / $limite);

$query = "SELECT * FROM alunos $where ORDER BY created_at DESC LIMIT $limite OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<h3><i class="bi bi-person-workspace me-2"></i> Gestão de Alunos</h3>
<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <input type="text" name="nome" value="<?= htmlspecialchars($nomeFiltro) ?>" placeholder="Pesquisar por nome" class="form-control">
  </div>
  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">Todos os status</option>
      <?php foreach ($statuses as $s): ?>
        <option value="<?= $s ?>" <?= $s === $statusFiltro ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="categoria" class="form-select">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= $c ?>" <?= $c === $categoriaFiltro ? 'selected' : '' ?>><?= $c ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-grid">
    <button class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filtrar</button>
  </div>
</form>

<?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<!-- Botão e Modal de Adicionar -->
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
  <i class="bi bi-person-plus"></i> Novo Aluno
</button>

<!-- Modal Adicionar Aluno -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Novo Aluno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input name="nome" class="form-control mb-2" placeholder="Nome" required>
        <input name="bi" class="form-control mb-2" placeholder="Nº do BI" required>
        <select name="sexo" class="form-select mb-2" required>
          <option value="">Sexo</option>
          <option value="Masculino">Masculino</option>
          <option value="Feminino">Feminino</option>
        </select>
        <input name="telefone" class="form-control mb-2" placeholder="Telefone">
        <select name="categoria" class="form-select" required>
          <option value="">Categoria</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c ?>"><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button name="salvar" class="btn btn-success"><i class="bi bi-plus-circle"></i> Adicionar</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive border shadow-sm rounded">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Nome</th>
        <th>BI</th>
        <th>Sexo</th>
        <th>Telefone</th>
        <th>Categoria</th>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($alunos): $n = $offset + 1;
        foreach ($alunos as $a): ?>
          <tr>
            <td><?= $n++ ?></td>
            <td><?= htmlspecialchars($a['nome']) ?></td>
            <td><?= htmlspecialchars($a['bi']) ?></td>
            <td><?= $a['sexo'] ?></td>
            <td><?= htmlspecialchars($a['telefone']) ?></td>
            <td><?= $a['categoria'] ?></td>
            <td><span class="badge <?= $a['status'] === 'Finalizado' ? 'bg-success' : ($a['status'] === 'Exame' ? 'bg-info' : 'bg-warning') ?>"> <?= $a['status'] ?> </span></td>
            <td>
              <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $a['id'] ?>"><i class="fas fa-edit me-1"></i></button>
              <a href="?delete=<?= $a['id'] ?>" onclick="return confirm('Confirma exclusão?')" class="btn btn-danger btn-sm">      <i class="fa fa-trash"></i></a>

              <div class="modal fade" id="editModal<?= $a['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <form method="POST" class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Editar Aluno</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= $a['id'] ?>">
                      <input name="nome" class="form-control mb-2" value="<?= htmlspecialchars($a['nome']) ?>" required>
                      <input name="bi" class="form-control mb-2" value="<?= htmlspecialchars($a['bi']) ?>" required>
                      <select name="sexo" class="form-select mb-2">
                        <?php foreach ($sexos as $sx): ?>
                          <option value="<?= $sx ?>" <?= $sx === $a['sexo'] ? 'selected' : '' ?>><?= $sx ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input name="telefone" class="form-control mb-2" value="<?= htmlspecialchars($a['telefone']) ?>">
                      <select name="categoria" class="form-select mb-2">
                        <?php foreach ($categorias as $c): ?>
                          <option value="<?= $c ?>" <?= $c === $a['categoria'] ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                      </select>
                      <select name="status" class="form-select">
                        <?php foreach ($statuses as $s): ?>
                          <option value="<?= $s ?>" <?= $s === $a['status'] ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" name="editar" class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
                    </div>
                  </form>
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach;
      else: ?>
        <tr>
          <td colspan="8" class="text-center">Nenhum aluno encontrado.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
      <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
        <a class="page-link" href="?pagina=<?= $i ?>&status=<?= urlencode($statusFiltro) ?>&categoria=<?= urlencode($categoriaFiltro) ?>&nome=<?= urlencode($nomeFiltro) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Novo Aluno</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input name="nome" class="form-control mb-2" placeholder="Nome" required>
        <input name="bi" class="form-control mb-2" placeholder="Nº BI" required>
        <select name="sexo" class="form-select mb-2">
          <option value="">Sexo</option>
          <?php foreach ($sexos as $sx): ?>
            <option value="<?= $sx ?>"><?= $sx ?></option>
          <?php endforeach; ?>
        </select>
        <input name="telefone" class="form-control mb-2" placeholder="Telefone">
        <select name="categoria" class="form-select">
          <option value="">Categoria</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c ?>"><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button name="salvar" class="btn btn-success"><i class="bi bi-plus-circle"></i> Adicionar</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>