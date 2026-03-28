<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Apenas administradores podem acessar esta página
if ($_SESSION['user']['tipo'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Acesso negado.');
}

// Carrega lista de usuários para seleção
$usuarios = $conn->query("SELECT id, nome FROM users ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Handlers de formulário
if (isset($_POST['salvar_salario'])) {
    $userId = intval($_POST['user_id']);
    $valor  = floatval($_POST['salario']);
    // Insere na tabela faturas_salarios
    $stmt = $conn->prepare(
        "INSERT INTO faturas_salarios (user_id, salario, data, emitido_por) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $valor, date('Y-m-d'), $_SESSION['user']['id']]);
    header('Location: salarios.php?success_salario=1');
    exit;
}

if (isset($_POST['salvar_divida'])) {
    $dividaId  = $_POST['divida_id'] ?? null;
    $userId    = intval($_POST['user_id_divida']);
    $descricao = trim($_POST['descricao_divida']);
    $valor     = floatval($_POST['valor_divida']);
    $data      = $_POST['data_divida'] ?? date('Y-m-d');

    if ($dividaId) {
        // Atualiza dívida existente
        $stmt = $conn->prepare(
            "UPDATE dividas SET user_id = ?, descricao = ?, quantidade = ?, data = ? WHERE id = ?"
        );
        $stmt->execute([$userId, $descricao, $valor, $data, $dividaId]);
    } else {
        // Insere nova dívida
        $stmt = $conn->prepare(
            "INSERT INTO dividas (user_id, descricao, quantidade, data) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $descricao, $valor, $data]);
    }
    header('Location: salarios.php?success_divida=1');
    exit;
}

if (isset($_GET['delete_salario'])) {
    $id = intval($_GET['delete_salario']);
    $conn->prepare("DELETE FROM faturas_salarios WHERE id = ?")->execute([$id]);
    header('Location: salarios.php?deleted_salario=1');
    exit;
}

if (isset($_GET['delete_divida'])) {
    $id = intval($_GET['delete_divida']);
    $conn->prepare("DELETE FROM dividas WHERE id = ?")->execute([$id]);
    header('Location: salarios.php?deleted_divida=1');
    exit;
}

// Consulta de salários e dívidas
$salarios = $conn->query(
    "SELECT fs.id, fs.user_id, fs.salario, fs.data, u.nome AS funcionario, emis.nome AS emitido_por_nome
     FROM faturas_salarios fs
     JOIN users u ON fs.user_id = u.id
     JOIN users emis ON fs.emitido_por = emis.id
     ORDER BY fs.data DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$dividas = $conn->query(
    "SELECT d.id, d.user_id, d.quantidade, d.descricao, d.data, u.nome AS funcionario
     FROM dividas d
     JOIN users u ON d.user_id = u.id
     ORDER BY d.data DESC"
)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h3>Gestão de Salários e Dívidas</h3>

<?php if (isset($_GET['success_salario'])): ?><div class="alert alert-success">Salário emitido!</div><?php endif; ?>
<?php if (isset($_GET['success_divida'])): ?><div class="alert alert-success">Dívida registrada!</div><?php endif; ?>
<?php if (isset($_GET['deleted_salario'])): ?><div class="alert alert-warning">Fatura de salário removida.</div><?php endif; ?>
<?php if (isset($_GET['deleted_divida'])): ?><div class="alert alert-warning">Dívida removida.</div><?php endif; ?>

<div class="mb-3 d-flex gap-2">
  <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalSalario">
    <i class="fa fa-wallet"></i> Nova Fatura Salário
  </button>
  <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDivida">
    <i class="fa fa-credit-card"></i> Registrar Dívida
  </button>
  <a href="salarios_pdf.php" class="btn btn-primary" target="_blank">
  <i class="fa fa-file-pdf"></i> PDF
</a>

</div>

<div class="row">
  <!-- Tabela de Salários -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header bg-success text-white">Faturas de Salários</div>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead><tr><th>#</th><th>Funcionário</th><th>Salário</th><th>Data</th><th>Emitido Por</th><th>Ações</th></tr></thead>
          <tbody>
            <?php foreach ($salarios as $i => $s): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($s['funcionario']) ?></td>
              <td><?= number_format($s['salario'],2,',','.') ?></td>
              <td><?= date('d/m/Y', strtotime($s['data'])) ?></td>
              <td><?= htmlspecialchars($s['emitido_por_nome']) ?></td>
              <td>
                <a href="?delete_salario=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover fatura?')">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Tabela de Dívidas -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header bg-danger text-white">Dívidas</div>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead><tr><th>#</th><th>Funcionário</th><th>Valor</th><th>Descrição</th><th>Data</th><th>Ações</th></tr></thead>
          <tbody>
            <?php foreach ($dividas as $j => $d): ?>
            <tr>
              <td><?= $j+1 ?></td>
              <td><?= htmlspecialchars($d['funcionario']) ?></td>
              <td><?= number_format($d['quantidade'],2,',','.') ?></td>
              <td><?= htmlspecialchars($d['descricao']) ?></td>
              <td><?= date('d/m/Y', strtotime($d['data'])) ?></td>
              <td>
                <a href="?delete_divida=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover dívida?')">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Salário -->
<div class="modal fade" id="modalSalario" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Emitir Fatura Salário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="salvar_salario" value="1">
        <div class="mb-3">
          <label>Funcionário</label>
          <select name="user_id" class="form-select" required>
            <option value="">Selecione</option>
            <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Salário (Dbs)</label>
          <input type="number" name="salario" step="0.01" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Emitir</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Dívida -->
<div class="modal fade" id="modalDivida" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Registrar Dívida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="salvar_divida" value="1">
        <div class="mb-3">
          <label>Funcionário</label>
          <select name="user_id_divida" class="form-select" required>
            <option value="">Selecione</option>
            <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Descrição</label>
          <input type="text" name="descricao_divida" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Valor (Dbs)</label>
          <input type="number" name="valor_divida" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Data</label>
          <input type="date" name="data_divida" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
