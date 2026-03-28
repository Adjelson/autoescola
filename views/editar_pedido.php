<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("ID inválido.");

$stmt = $conn->prepare("SELECT * FROM pedidos_licenca WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pedido) exit("Pedido não encontrado.");

$ref = $pedido['ref_numero'];
$alunoNomes = json_decode($pedido['alunos'], true);

// Buscar todos os alunos
$alunos = $conn->query("SELECT id, nome, categoria FROM alunos")->fetchAll(PDO::FETCH_ASSOC);

// Pré-selecionar os alunos existentes
$selecionados = [];
foreach ($alunos as $a) {
  if (in_array($a['nome'], $alunoNomes)) {
    $selecionados[] = $a;
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
      <h5 class="mb-0">✏️ Editar Pedido #<?= str_pad($ref, 3, '0', STR_PAD_LEFT) ?>/ECQ/STP</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="salvar_edicao.php">
        <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Número do Documento (Ref. nº)</label>
          <input type="number" name="ref_numero" class="form-control" value="<?= $ref ?>" required>
        </div>

        <label class="form-label">Pesquisar Aluno</label>
        <div class="input-group mb-2">
          <input type="text" id="searchAluno" class="form-control" placeholder="Digite o nome...">
          <button type="button" class="btn btn-outline-success" id="btnAdicionar" disabled>➕ Adicionar</button>
        </div>
        <ul id="sugestoes" class="list-group mb-2"></ul>

        <input type="hidden" name="alunos" id="alunosSelecionados">
        <div>
          <strong>Selecionados:</strong>
          <ul id="listaFinal" class="list-group mt-2"></ul>
        </div>

        <div class="text-end mt-3">
          <button class="btn btn-warning">💾 Atualizar Pedido</button>
          <a href="pedido_licenca.php" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let selecionados = <?= json_encode($selecionados) ?>;
let sugestaoAtual = null;

document.getElementById('searchAluno').addEventListener('input', async function () {
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

atualizarLista();
</script>

<?php include '../includes/footer.php'; ?>
