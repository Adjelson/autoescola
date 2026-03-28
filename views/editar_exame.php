<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if (!isset($_GET['id'])) exit("ID não especificado.");

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM pedidos_exame WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) exit("Pedido não encontrado.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = intval($_POST['ref_numero']);
    $dataExame = $_POST['data_exame'] ?? '';
    $assunto = $_POST['assunto'] ?? 'Exame Teórico e Prático';
    $alunosIds = array_filter(explode(',', $_POST['alunos'] ?? ''));
    $data = date('Y-m-d');

    if (!empty($alunosIds) && count($alunosIds) > 0 && count($alunosIds) <= 10) {
        $placeholders = implode(',', array_fill(0, count($alunosIds), '?'));
        $stmt = $conn->prepare("SELECT id, nome, categoria FROM alunos WHERE id IN ($placeholders)");

        if ($stmt->execute(array_values($alunosIds))) {
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Garantir que os dados sejam mantidos se não forem alterados
            if (empty($alunos)) {
                $alunos = json_decode($pedido['alunos'], true) ?? [];
            }

            $stmt = $conn->prepare("UPDATE pedidos_exame SET ref_numero = ?, data_exame = ?, assunto = ?, alunos = ?, data_emissao = ? WHERE id = ?");
            $stmt->execute([$ref, $dataExame, $assunto, json_encode($alunos), $data, $id]);

            header("Location: pedido_exame.php");
            exit;
        } else {
            $erro = "Erro ao buscar os dados dos alunos.";
        }
    } else {
        // Se não foram enviados alunos, reutiliza os existentes
        $alunos = json_decode($pedido['alunos'], true) ?? [];
        $stmt = $conn->prepare("UPDATE pedidos_exame SET ref_numero = ?, data_exame = ?, assunto = ?, alunos = ?, data_emissao = ? WHERE id = ?");
        $stmt->execute([$ref, $dataExame, $assunto, json_encode($alunos), $data, $id]);

        header("Location: pedido_exame.php");
        exit;
    }
}

$alunosExistentes = json_decode($pedido['alunos'], true) ?? [];
$idsExistentes = array_column($alunosExistentes, 'id');

$dadosAlunosCompletos = [];
if (!empty($idsExistentes)) {
    $placeholders = implode(',', array_fill(0, count($idsExistentes), '?'));
    $stmt = $conn->prepare("SELECT id, nome, categoria FROM alunos WHERE id IN ($placeholders)");
    $stmt->execute(array_values($idsExistentes));
    $dadosAlunosCompletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
      <h5 class="mb-0">✏️ Editar Pedido de Exame Extra</h5>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Número do Documento (Ref. nº)</label>
          <input type="number" name="ref_numero" class="form-control" required value="<?= $pedido['ref_numero'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Data do Exame</label>
          <input type="date" name="data_exame" class="form-control" required value="<?= $pedido['data_exame'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Assunto do Exame</label>
          <select name="assunto" class="form-select" required>
            <option value="Exame Teórico" <?= $pedido['assunto'] === 'Exame Teórico' ? 'selected' : '' ?>>Exame Teórico</option>
            <option value="Exame Prático" <?= $pedido['assunto'] === 'Exame Prático' ? 'selected' : '' ?>>Exame Prático</option>
            <option value="Exame Teórico e Prático" <?= $pedido['assunto'] === 'Exame Teórico e Prático' ? 'selected' : '' ?>>Exame Teórico e Prático</option>
          </select>
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
          <button class="btn btn-warning">💾 Guardar Alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let selecionados = <?= json_encode(array_map(function($a) {
  return [
    'id' => $a['id'] ?? null,
    'nome' => $a['nome'],
    'categoria' => $a['categoria']
  ];
}, $alunosExistentes)) ?>;

let sugestaoAtual = null;

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

atualizarLista();
</script>

<?php include '../includes/footer.php'; ?>
