<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$tipoUser = $_SESSION['user']['tipo'];
$userId   = $_SESSION['user']['id'];

$tipo       = $_GET['tipo'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim    = $_GET['data_fim'] ?? '';
$page       = max(1, intval($_GET['page'] ?? 1));
$limit      = 10;
$offset     = ($page - 1) * $limit;

// Query base
$query = "
  SELECT t.*, a.nome AS aluno_nome, u.nome AS user_nome
  FROM transacoes t
  LEFT JOIN alunos a ON t.aluno_id = a.id
  LEFT JOIN users u ON t.user_id = u.id
  WHERE 1
";

$params = [];

if ($tipoUser !== 'admin') {
  $query .= " AND t.user_id = ?";
  $params[] = $userId;
}

if ($tipo && in_array($tipo, ['receita','despesa'])) {
  $query .= " AND t.tipo = ?";
  $params[] = $tipo;
}
if ($dataInicio) {
  $query .= " AND t.data >= ?";
  $params[] = $dataInicio;
}
if ($dataFim) {
  $query .= " AND t.data <= ?";
  $params[] = $dataFim;
}

// Contagem total
$countQuery = str_replace("SELECT t.*, a.nome AS aluno_nome, u.nome AS user_nome", "SELECT COUNT(*)", $query);
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$totalRegistros = $countStmt->fetchColumn();

// Resultado com ordenação e paginação
$query .= " ORDER BY t.data DESC, t.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculo dos totais
$totalReceita = $totalDespesa = 0;
foreach ($transacoes as $t) {
  if ($t['tipo'] === 'receita') $totalReceita += $t['valor'];
  if ($t['tipo'] === 'despesa') $totalDespesa += $t['valor'];
}
$saldo = $totalReceita - $totalDespesa;
$totalPaginas = ceil($totalRegistros / $limit);
?>

<?php include '../includes/header.php'; ?>

<h3 class="mb-4">📑 Histórico de Transações <?= $tipoUser === 'admin' ? '(Todos os Utilizadores)' : '' ?></h3>

<form method="GET" class="row g-3 mb-4">
  <div class="col-md-3">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select">
      <option value="">Todos</option>
      <option value="receita" <?= $tipo === 'receita' ? 'selected' : '' ?>>Receita</option>
      <option value="despesa" <?= $tipo === 'despesa' ? 'selected' : '' ?>>Despesa</option>
    </select>
  </div>
  <div class="col-md-3">
    <label>Data Início</label>
    <input type="date" name="data_inicio" value="<?= $dataInicio ?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label>Data Fim</label>
    <input type="date" name="data_fim" value="<?= $dataFim ?>" class="form-control">
  </div>
  <div class="col-md-3 d-flex align-items-end gap-2">
    <button class="btn btn-primary">Filtrar</button>
    <a href="transacoes.php" class="btn btn-secondary">Limpar</a>
    <a href="relatorio_transacoes.php?tipo=<?= urlencode($tipo) ?>&data_inicio=<?= urlencode($dataInicio) ?>&data_fim=<?= urlencode($dataFim) ?>" target="_blank" class="btn btn-outline-dark">📄 PDF</a>
  </div>
</form>

<div class="card shadow-sm">
   <div class="table-responsive border shadow-sm rounded">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-primary">
        <tr>
          <th>Tipo</th>
          <th>Aluno</th>
          <th>Descrição</th>
          <th>Valor</th>
          <th>Data</th>
          <th>Registado por</th>
          <th>Criado em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transacoes as $t): ?>
        <tr>
          <td><span class="badge <?= $t['tipo'] === 'receita' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst($t['tipo']) ?></span></td>
          <td><?= htmlspecialchars($t['aluno_nome'] ?? '-') ?></td>
          <td><?= htmlspecialchars($t['descricao']) ?></td>
          <td>R$ <?= number_format($t['valor'], 2, ',', '.') ?></td>
          <td><?= date('d/m/Y', strtotime($t['data'])) ?></td>
          <td><?= $t['user_nome'] ?? '-' ?></td>
          <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-secondary">
        <tr>
          <th colspan="3">Totais:</th>
          <th class="text-success">+R$ <?= number_format($totalReceita, 2, ',', '.') ?></th>
          <th class="text-danger">-R$ <?= number_format($totalDespesa, 2, ',', '.') ?></th>
          <th colspan="2" class="text-primary">Saldo: R$ <?= number_format($saldo, 2, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Paginação -->
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>&tipo=<?= urlencode($tipo) ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

<?php include '../includes/footer.php'; ?>
