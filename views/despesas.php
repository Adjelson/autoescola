<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// Permissões
$userId   = $_SESSION['user']['id'];
$userTipo = $_SESSION['user']['tipo']; // 'admin' ou outro

// Paginação
$limite      = 15;
$pagina      = max(1, intval($_GET['pagina'] ?? 1));
$offset      = ($pagina - 1) * $limite;

// Filtros
$categoriaFiltro = $_GET['categoria'] ?? '';
$dataDe          = $_GET['de']        ?? '';
$dataAte         = $_GET['ate']       ?? '';

// Categorias definidas
$categorias = [
    'exame'             => 'Exame',
    'combustivel_carro' => 'Combustível Carro',
    'combustivel_mota'  => 'Combustível Moto',
    'licenca'           => 'Licença',
    'outras'            => 'Outras',
];

// Ações de Inserção/Edição
if (isset($_POST['salvar'])) {
    $id         = $_POST['id']          ?? null;
    $categoria  = $_POST['categoria']   ?? '';
    $descricao  = trim($_POST['descricao'] ?? '');
    $valor      = floatval($_POST['valor']    ?? 0);
    $data       = date('Y-m-d');

    if ($categoria && $descricao && $valor > 0) {
        if ($id) {
            // Atualiza transacoes
            $sqlTrans = "UPDATE transacoes SET categoria=?, descricao=?, valor=?, data=? WHERE id=? AND tipo='despesa'";
            $paramsTrans = [$categoria, $descricao, $valor, $data, $id];
            if ($userTipo !== 'admin') {
                $sqlTrans .= " AND user_id=?";
                $paramsTrans[] = $userId;
            }
            $stmt = $conn->prepare($sqlTrans);
            $stmt->execute($paramsTrans);

            // Atualiza tabela despesas
            $sqlDesp = "UPDATE despesas SET categoria=?, descricao=?, valor=?, data=? WHERE id=? AND user_id=?";
            $stmt2 = $conn->prepare($sqlDesp);
            $stmt2->execute([$categoria, $descricao, $valor, $data, $id, $userId]);
        } else {
            // Insere em transacoes
            $stmt = $conn->prepare(
                "INSERT INTO transacoes (tipo,categoria,descricao,valor,data,user_id,created_at) VALUES ('despesa',?,?,?,?,?,NOW())"
            );
            $stmt->execute([$categoria, $descricao, $valor, $data, $userId]);

            // Insere em despesas
            $stmt2 = $conn->prepare(
                "INSERT INTO despesas (categoria,descricao,valor,data,user_id,criado_em) VALUES (?,?,?,?,?,NOW())"
            );
            $stmt2->execute([$categoria, $descricao, $valor, $data, $userId]);
        }
        header("Location: despesas.php?success=1");
        exit;
    } else {
        $erro = 'Todos os campos são obrigatórios.';
    }
}

// Exclusão
if (isset($_GET['excluir'])) {
    $idExcluir = intval($_GET['excluir']);
    // Excluir de transacoes
    $sqlTrans = "DELETE FROM transacoes WHERE id=? AND tipo='despesa'";
    $paramsTrans = [$idExcluir];
    if ($userTipo !== 'admin') {
        $sqlTrans .= " AND user_id=?";
        $paramsTrans[] = $userId;
    }
    $stmt = $conn->prepare($sqlTrans);
    $stmt->execute($paramsTrans);
    // Excluir de despesas
    $sqlDesp = "DELETE FROM despesas WHERE id=?";
    $stmt2 = $conn->prepare($sqlDesp);
    $stmt2->execute([$idExcluir]);

    header("Location: despesas.php?success=1");
    exit;
}

// Contagem para paginação
$params    = [];
$queryBase = "FROM despesas d";
if ($userTipo !== 'admin') {
    $queryBase .= " WHERE d.user_id=?";
    $params[] = $userId;
}
if ($categoriaFiltro) {
    $queryBase .= ($userTipo !== 'admin' ? " AND" : " WHERE") . " d.categoria=?";
    $params[] = $categoriaFiltro;
}
if ($dataDe && $dataAte) {
    $queryBase .= ($userTipo !== 'admin' || $categoriaFiltro ? " AND" : " WHERE") . " d.data BETWEEN ? AND ?";
    $params[] = $dataDe;
    $params[] = $dataAte;
}

$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total " . $queryBase);
$stmtTotal->execute($params);
$totalReg = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalReg / $limite);

// Busca paginada da tabela despesas
$query = "SELECT * " . $queryBase . " ORDER BY d.data DESC LIMIT $limite OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include '../includes/header.php'; ?>
<h3><i class="bi bi-wallet2"></i> Despesas</h3>
<?php if (!empty($_GET['success'])): ?><div class="alert alert-success">Operação realizada com sucesso!</div><?php endif; ?>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<!-- Filtros -->
<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <label>Categoria</label>
    <select name="categoria" class="form-select">
      <option value="">Todas</option>
      <?php foreach ($categorias as $key => $label): ?>
        <option value="<?= $key ?>" <?= $categoriaFiltro === $key ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label>De</label>
    <input type="date" name="de" value="<?= $dataDe ?>" class="form-control">
  </div>
  <div class="col-md-2">
    <label>Até</label>
    <input type="date" name="ate" value="<?= $dataAte ?>" class="form-control">
  </div>
  <div class="col-md-5 d-flex align-items-end">
    <button class="btn btn-primary me-2">Filtrar</button>
   
    <a href="despesas.php" class="btn btn-outline-secondary ms-2">Limpar</a>
  </div> <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDespesa">
      <i class="bi bi-plus-circle me-1"></i> Nova Despesa
    </button>
</form>

<!-- Tabela de Despesas -->
<?php if ($despesas): ?>
<div class="table-responsive border shadow-sm rounded">
  <table class="table table-hover mb-0 align-middle">
    <thead class="table-primary">
      <tr>
        <th>#</th><th>Categoria</th><th>Descrição</th><th>Valor</th><th>Data</th><th>Criado Em</th><th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php $n = $offset + 1; foreach ($despesas as $d): ?>
      <tr>
        <td><?= $n++ ?></td>
        <td><?= htmlspecialchars($categorias[$d['categoria']] ?? ucfirst($d['categoria'])) ?></td>
        <td><?= htmlspecialchars($d['descricao']) ?></td>
        <td>R$ <?= number_format($d['valor'], 2, ',', '.') ?></td>
        <td><?= date('d/m/Y', strtotime($d['data'])) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($d['criado_em'])) ?></td>
        <td>
          <button class="btn btn-sm btn-outline-info editarBtn" data-id="<?= $d['id'] ?>" data-categoria="<?= $d['categoria'] ?>" data-descricao="<?= htmlspecialchars($d['descricao']) ?>" data-valor="<?= $d['valor'] ?>" data-bs-toggle="modal" data-bs-target="#modalDespesa">
            <i class="fas fa-edit"></i>
          </button>
          <a href="?excluir=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Eliminar esta despesa?')">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Paginação -->
<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
      <li class="page-item <?= $p == $pagina ? 'active' : '' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $p])) ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php else: ?>
  <div class="alert alert-info">Nenhuma despesa encontrada.</div>
<?php endif; ?>

<!-- Modal de Despesa -->
<div class="modal fade" id="modalDespesa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Despesa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="despesaId">
        <div class="mb-3">
          <label>Categoria</label>
          <select name="categoria" id="despesaCategoria" class="form-select" required>
            <option value="">Selecione</option>
            <?php foreach ($categorias as $key => $label): ?>
              <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Descrição</label>
          <input type="text" name="descricao" id="despesaDescricao" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Valor</label>
          <input type="number" name="valor" id="despesaValor" step="0.01" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="salvar" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
// Preenche modal para edição
document.querySelectorAll('.editarBtn').forEach(btn => btn.addEventListener('click', () => {
  document.getElementById('despesaId').value = btn.dataset.id;
  document.getElementById('despesaCategoria').value = btn.dataset.categoria;
  document.getElementById('despesaDescricao').value = btn.dataset.descricao;
  document.getElementById('despesaValor').value = btn.dataset.valor;
}));
</script>

<?php include '../includes/footer.php'; ?>
