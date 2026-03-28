<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['filter'])) {
  $ref = intval($_POST['ref_numero']);
  $dataExame = $_POST['data_exame'] ?? '';
  $tipo = $_POST['tipo'];
  $assunto = $_POST['assunto'] ?? 'Exame Teórico e Prático';
  $alunosIds = explode(',', $_POST['alunos'] ?? '');
  $data = date('Y-m-d');

  if (empty($alunosIds) || $alunosIds[0] === '') {
    $errorMessage = "Por favor, selecione pelo menos um aluno.";
  } elseif (count($alunosIds) > 10) {
    $errorMessage = "O limite máximo de 10 alunos foi excedido.";
  } else {
    $placeholders = implode(',', array_fill(0, count($alunosIds), '?'));
    $stmt = $conn->prepare("SELECT nome, categoria FROM alunos WHERE id IN ($placeholders)");
    $stmt->execute($alunosIds);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM pedidos_exame WHERE ref_numero = ?");
    $stmt->execute([$ref]);
    if ($stmt->fetchColumn() > 0) {
      $errorMessage = "Número de documento já utilizado.";
    } else {
      $stmt = $conn->prepare("INSERT INTO pedidos_exame (ref_numero, tipo, data_exame, assunto, alunos, data_emissao) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$ref, $tipo, $dataExame, $assunto, json_encode($alunos), $data]);

      $pedidoId = $conn->lastInsertId();
      $successMessage = "Pedido de exame criado com sucesso! Redirecionando para o PDF...";
      $pdfPath = $tipo === 'extra' ? "pedido_exame.php?id=$pedidoId" : "pdf_exame_normal.php?id=$pedidoId";
      header("Refresh:2; url=$pdfPath");
    }
  }
}

// Handle filters
$whereClauses = [];
$filterParams = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
  if (!empty($_POST['filter_ref'])) {
    $whereClauses[] = "ref_numero = ?";
    $filterParams[] = intval($_POST['filter_ref']);
  }
  if (!empty($_POST['filter_data_inicio']) && !empty($_POST['filter_data_fim'])) {
    $whereClauses[] = "data_exame BETWEEN ? AND ?";
    $filterParams[] = $_POST['filter_data_inicio'];
    $filterParams[] = $_POST['filter_data_fim'];
  }
  if (!empty($_POST['filter_tipo']) && $_POST['filter_tipo'] !== 'todos') {
    $whereClauses[] = "tipo = ?";
    $filterParams[] = $_POST['filter_tipo'];
  }
  if (!empty($_POST['filter_assunto']) && $_POST['filter_assunto'] !== 'todos') {
    $whereClauses[] = "assunto = ?";
    $filterParams[] = $_POST['filter_assunto'];
  }
}

$sql = "SELECT * FROM pedidos_exame";
if (!empty($whereClauses)) {
  $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($filterParams);
$pedidos = $stmt->fetchAll();

include '../includes/header.php';
?> 
<?php if ($successMessage): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($successMessage) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<?php if ($errorMessage): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($errorMessage) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<div class="card-header" style="display: flex;">
  <h5 style="width: 70%;">Pedido de Exame</h5>

</div>
<div >
  <div class="modal fade" id="formularioModal" tabindex="-1" aria-labelledby="formularioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="formularioModalLabel">Formulário de Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body">
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Número do Documento (Ref. nº)</label>
                <input type="number" name="ref_numero" class="form-control" required>
              </div>
              <div style="display: flex;">
                <div class="mb-3">
                  <label class="form-label">Tipo de Exame</label>
                  <select name="tipo" class="form-select" required>
                    <option value="normal">Normal</option>
                    <option value="extra">Extra</option>
                  </select>
                </div>
                <div class="mb-3 " style="margin-left: 20px;">
                  <label class="form-label">Data do Exame</label>
                  <input type="date" name="data_exame" class="form-control" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Assunto do Exame</label>
                <select name="assunto" class="form-select" required>
                  <option value="Exame Teórico">Exame Teórico</option>
                  <option value="Exame Prático">Exame Prático</option>
                  <option value="Exame Teórico e Prático" selected>Exame Teórico e Prático</option>
                </select>
              </div>
              <label class="form-label">Pesquisar Aluno</label>
              <div class="input-group mb-2 text-black">
                <input type="text" id="searchAluno" class="form-control" placeholder="Digite o nome...">
                <button type="button" class="btn btn-outline-success" id="btnAdicionar" disabled>➕ Adicionar</button>
              </div>
              <ul id="sugestoes" class="list-group mb-2 text-black"></ul>
              <input type="hidden" name="alunos" id="alunosSelecionados">
              <div>
                <strong>Selecionados:</strong>
                <ul id="listaFinal" class="list-group mt-2"></ul>
              </div>
              <div class="text-end mt-3">
                <button type="submit" class="btn btn-success">📄 Gerar Pedido</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>



  <div class="card-body" class="row g-2 mb-3">
    <form method="POST">
      <input type="hidden" name="filter" value="1">
      <div class="row">
        <div class="col-md-3 mb-3">
          <label class="form-label">Ref. nº</label>
          <input type="number" name="filter_ref" class="form-control" placeholder="ex:1" value="<?= isset($_POST['filter_ref']) ? htmlspecialchars($_POST['filter_ref']) : '' ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">Data Início</label>
          <input type="date" name="filter_data_inicio" class="form-control" value="<?= isset($_POST['filter_data_inicio']) ? htmlspecialchars($_POST['filter_data_inicio']) : '' ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">Data Fim</label>
          <input type="date" name="filter_data_fim" class="form-control" value="<?= isset($_POST['filter_data_fim']) ? htmlspecialchars($_POST['filter_data_fim']) : '' ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">Tipo</label>
          <select name="filter_tipo" class="form-select">
            <option value="todos">Todos</option>
            <option value="normal" <?= isset($_POST['filter_tipo']) && $_POST['filter_tipo'] === 'normal' ? 'selected' : '' ?>>Normal</option>
            <option value="extra" <?= isset($_POST['filter_tipo']) && $_POST['filter_tipo'] === 'extra' ? 'selected' : '' ?>>Extra</option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Assunto</label>
          <select name="filter_assunto" class="form-select">
            <option value="todos">Todos</option>
            <option value="Exame Teórico" <?= isset($_POST['filter_assunto']) && $_POST['filter_assunto'] === 'Exame Teórico' ? 'selected' : '' ?>>Exame Teórico</option>
            <option value="Exame Prático" <?= isset($_POST['filter_assunto']) && $_POST['filter_assunto'] === 'Exame Prático' ? 'selected' : '' ?>>Exame Prático</option>
            <option value="Exame Teórico e Prático" <?= isset($_POST['filter_assunto']) && $_POST['filter_assunto'] === 'Exame Teórico e Prático' ? 'selected' : '' ?>>Exame Teórico e Prático</option>
          </select>
        </div>
      </div>
      <div class="text-end"> <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#formularioModal">
          Abrir Formulário
        </button>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Limpar</a>
      </div>
    </form>
  </div>


  <div class="card mt-4">
    <div class="table-responsive border shadow-sm rounded">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-primary">
          <tr>
            <th>Ref. nº</th>
            <th>Data Exame</th>
            <th>Assunto</th>
            <th>Tipo</th>
            <th>Alunos</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $p): ?>
            <tr>
              <td><?= str_pad($p['ref_numero'], 3, '0', STR_PAD_LEFT) ?>/ECQ/STP</td>
              <td><?= date('d/m/Y', strtotime($p['data_exame'])) ?></td>
              <td><?= htmlspecialchars($p['assunto']) ?></td>
              <td><?= ucfirst($p['tipo']) ?></td>
              <td>
                <ul class="mb-0">
                  <?php foreach (json_decode($p['alunos'], true) as $aluno): ?>
                    <li><?= htmlspecialchars($aluno['nome']) ?> (<?= htmlspecialchars($aluno['categoria']) ?>)</li>
                  <?php endforeach; ?>
                </ul>
              </td>
              <td>
                <a href="pdf_exame_<?= $p['tipo'] ?>.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">PDF</a>
                <a href="editar_exame.php?id=<?= $p['id'] ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Editar</a>
                <a href="eliminar_exame.php?id=<?= $p['id'] ?>" onclick="return confirm('Deseja realmente eliminar este pedido?')" class="btn btn-outline-danger btn-sm">      <i class="fa fa-trash"></i>Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  let selecionados = [];
  let sugestaoAtual = null;

  document.getElementById('searchAluno').addEventListener('input', async function() {
    const query = this.value.trim();
    const sugestoes = document.getElementById('sugestoes');
    sugestoes.innerHTML = '';
    document.getElementById('btnAdicionar').disabled = true;

    if (query.length < 1) return;

    const res = await fetch('../ajax/busca_aluno.php?q=' + encodeURIComponent(query));
    const data = await res.json();

    data.forEach(aluno => {
      const li = document.createElement('li');
      li.className = 'list-group-item list-group-item-action text-black';
      li.textContent = `${aluno.nome} (${aluno.categoria})`;
      li.onclick = () => {
        sugestaoAtual = aluno;
        document.getElementById('searchAluno').value = aluno.nome;
        document.getElementById('btnAdicionar').disabled = false;
      };
      sugestoes.appendChild(li);
    });
  });

  document.getElementById('btnAdicionar').addEventListener('click', () => {
    if (!sugestaoAtual || selecionados.find(a => a.id == sugestaoAtual.id)) return;
    if (selecionados.length >= 10) {
      alert("Limite de 10 alunos atingido.");
      return;
    }
    selecionados.push(sugestaoAtual);
    atualizarLista();
    document.getElementById('searchAluno').value = '';
    document.getElementById('sugestoes').innerHTML = '';
    document.getElementById('btnAdicionar').disabled = true;
  });

  function atualizarLista() {
    const lista = document.getElementById('listaFinal');
    lista.innerHTML = '';
    selecionados.forEach((a, i) => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between text-black';
      li.innerHTML = `${a.nome} (${a.categoria}) <button type="button" class="btn btn-sm btn-danger" onclick="remover(${i})">✖</button>`;
      lista.appendChild(li);
    });
    document.getElementById('alunosSelecionados').value = selecionados.map(a => a.id).join(',');
  }

  function remover(index) {
    selecionados.splice(index, 1);
    atualizarLista();
  }
</script>

<?php include '../includes/footer.php'; ?>