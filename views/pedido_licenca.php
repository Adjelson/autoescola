<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$error = '';
$success = '';

// Função para sanitização básica
function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// PROCESSAR NOVO PEDIDO OU ATUALIZAÇÃO
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' 
    && empty($_POST['filter'])
) {
    // Colete e valide campos
    $ref      = filter_input(INPUT_POST, 'ref_numero', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $idsRaw   = sanitize($_POST['alunos'] ?? '');
    $alunosIds = array_filter(array_map('intval', explode(',', $idsRaw)), fn($i) => $i > 0);
    $hoje     = (new DateTime())->format('Y-m-d');

    if (!$ref) {
        $error = 'Por favor, insira um número de referência válido (maior que zero).';
    } elseif (empty($alunosIds)) {
        $error = 'Selecione pelo menos um aluno para o pedido.';
    } elseif (count($alunosIds) > 10) {
        $error = 'Você pode selecionar no máximo 10 alunos por pedido.';
    } else {
        try {
            $conn->beginTransaction();

            // Buscar e validar alunos
            $ph = implode(',', array_fill(0, count($alunosIds), '?'));
            $stmt = $conn->prepare(
                "SELECT id, nome FROM alunos WHERE id IN ($ph)"
            );
            $stmt->execute($alunosIds);
            $alunos = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

            if (count($alunos) !== count($alunosIds)) {
                throw new Exception('Um ou mais alunos não foram encontrados.');
            }

            // Verificar existência de pedido
            $check = $conn->prepare(
                'SELECT id FROM pedidos_licenca WHERE ref_numero = ?'
            );
            $check->execute([$ref]);
            $existingId = $check->fetchColumn();

            $jsonAlunos = json_encode($alunos, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                // Atualizar
                $upd = $conn->prepare(
                    'UPDATE pedidos_licenca SET alunos = ?, data_emissao = ? WHERE id = ?'
                );
                $upd->execute([$jsonAlunos, $hoje, $existingId]);
                $success = 'Pedido atualizado com sucesso!';
                header("Refresh:2; url=pdf_licenca.php?id=$existingId");
            } else {
                // Novo
                $ins = $conn->prepare(
                    'INSERT INTO pedidos_licenca (ref_numero, alunos, data_emissao) VALUES (?, ?, ?)'
                );
                $ins->execute([$ref, $jsonAlunos, $hoje]);
                $newId = $conn->lastInsertId();
                $success = 'Pedido criado com sucesso!';
                header("Refresh:2; url=pdf_licenca.php?id=$newId");
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Erro ao processar pedido: ' . sanitize($e->getMessage());
        }
    }
}

// FILTRAR PEDIDOS
$where = [];
$params = [];
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['filter'])
) {
    $refFilter  = filter_input(INPUT_POST, 'filter_ref', FILTER_VALIDATE_INT);
    $dtStart    = $_POST['filter_data_inicio'] ?? '';
    $dtEnd      = $_POST['filter_data_fim'] ?? '';

    if ($refFilter) {
        $where[] = 'ref_numero = ?';
        $params[] = $refFilter;
    }
    if ($dtStart && $dtEnd) {
        if (strtotime($dtStart) > strtotime($dtEnd)) {
            $error = 'A data inicial não pode ser posterior à data final.';
        } else {
            $where[] = 'data_emissao BETWEEN ? AND ?';
            $params[] = $dtStart;
            $params[] = $dtEnd;
        }
    }
    if (empty($where) && !$error) {
        $success = 'Filtros limpos.';
    } elseif (!$error) {
        $success = 'Filtros aplicados.';
    }
}

// Montar consulta
$sql = 'SELECT id, ref_numero, alunos, data_emissao FROM pedidos_licenca';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC';
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div >
  <h3 class="mb-3"><i class="fas fa-file-alt me-2"></i> Gestão de Pedidos de Licença</h3>

  <!-- Alertas -->
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible">
      <?= sanitize($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible">
      <?= sanitize($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Formulário de Filtro -->
  <form method="POST" class="row g-3 mb-4">
    <input type="hidden" name="filter" value="1">
    <div class="col-md-3">
      <label class="form-label">Ref. nº</label>
      <input type="number" name="filter_ref" class="form-control" min="1" value="<?= sanitize($_POST['filter_ref'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Data Início</label>
      <input type="date" name="filter_data_inicio" class="form-control" value="<?= sanitize($_POST['filter_data_inicio'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Data Fim</label>
      <input type="date" name="filter_data_fim" class="form-control" value="<?= sanitize($_POST['filter_data_fim'] ?? '') ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Filtrar</button>
      <a href="<?= basename(__FILE__) ?>" class="btn btn-secondary"><i class="fas fa-eraser me-1"></i> Limpar</a>
    </div>
  </form>

  <!-- Botão Novo Pedido -->
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#novoPedidoModal">
    <i class="fas fa-plus me-1"></i> Novo Pedido
  </button>

  <!-- Tabela de Pedidos -->
  <div class="table-responsive">
    <table class="table table-hover border">
      <thead class="table-primary">
        <tr>
          <th>Ref. nº</th>
          <th>Alunos</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pedidos)): ?>
          <tr><td colspan="4" class="text-center">Nenhum pedido encontrado.</td></tr>
        <?php else: ?>
          <?php foreach ($pedidos as $p): ?>
            <tr>
              <td><?= str_pad(sanitize($p['ref_numero']), 3, '0', STR_PAD_LEFT) ?>/ECQ/STP</td>
              <td>
                <ul class="mb-0">
                  <?php foreach (json_decode($p['alunos'], true) as $nome): ?>
                    <li><?= sanitize($nome) ?></li>
                  <?php endforeach; ?>
                </ul>
              </td>
              <td><?= (new DateTime($p['data_emissao']))->format('d/m/Y') ?></td>
              <td>
                <a href="pdf_licenca.php?id=<?=$p['id']?>" class="btn btn-outline-primary btn-sm" target="_blank">
                  <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
                <a href="editar_pedido.php?id=<?=$p['id']?>" class="btn btn-outline-warning btn-sm">
                  <i class="fas fa-edit me-1"></i> Editar
                </a>
                <a href="eliminar_pedido.php?id=<?=$p['id']?>" onclick="return confirm('Deseja eliminar este pedido?')" class="btn btn-outline-danger btn-sm">
                  <i class="fas fa-trash me-1"></i> Eliminar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal Novo Pedido -->
  <div class="modal fade" id="novoPedidoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Novo Pedido de Licença</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" id="formPedido" onsubmit="return validateForm()">
            <div class="mb-3">
              <label class="form-label">Ref. nº</label>
              <input type="number" name="ref_numero" id="ref_numero" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Pesquisar Aluno</label>
              <div class="input-group">
                <input type="text" id="searchAluno" class="form-control" placeholder="Nome do aluno...">
                <button type="button" id="btnAdicionar" class="btn btn-success" disabled>
                  <i class="fas fa-plus me-1"></i> Adicionar
                </button>
              </div>
            </div>

            <ul id="sugestoes" class="list-group mb-3"></ul>
            <ul id="listaFinal" class="list-group mb-3"></ul>
            <input type="hidden" name="alunos" id="alunosSelecionados">

            <div class="text-end">
              <button type="button" class="btn btn-secondary me-2" onclick="limparSelecao()">Limpar</button>
              <button type="submit" class="btn btn-success">Gerar Pedido</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchAluno');
    const listaSug = document.getElementById('sugestoes');
    const btnAdd = document.getElementById('btnAdicionar');
    const listaFinal = document.getElementById('listaFinal');
    let selecionados = [], current = null;

    function showAlert(msg, type) {
      const div = document.createElement('div');
      div.className = `alert alert-${type} alert-dismissible fade show`;
      div.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
      document.querySelector('.modal-body').prepend(div);
      setTimeout(() => div.classList.remove('show'), 2000);
    }

    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim();
      listaSug.innerHTML = '';
      btnAdd.disabled = true;
      if (q.length < 1) {
        listaSug.innerHTML = '<li class="list-group-item text-muted">Digite ao menos 2 caracteres.</li>';
        return;
      }
      fetch(`../ajax/busca_aluno.php?q=${encodeURIComponent(q)}`)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          listaSug.innerHTML = data.length
            ? data.map(a => `<li class="list-group-item list-group-item-action text-black" data-id="${a.id}" data-nome="${a.nome}">${a.nome} (${a.categoria})</li>`).join('')
            : '<li class="list-group-item text-muted">Nenhum aluno encontrado.</li>';
        })
        .catch(() => showAlert('Erro ao buscar alunos.', 'danger'));
    });

    listaSug.addEventListener('click', e => {
      const li = e.target.closest('li[data-id]');
      if (!li) return;
      const id = li.dataset.id, nome = li.dataset.nome;
      if (selecionados.find(a => a.id == id)) {
        showAlert('Aluno já selecionado.', 'warning'); return;
      }
      if (selecionados.length >= 10) {
        showAlert('Máximo de 10 alunos.', 'danger'); return;
      }
      selecionados.push({ id, nome });
      updateLista();
      btnAdd.disabled = true;
      searchInput.value = '';
      listaSug.innerHTML = '';
    });

    function updateLista() {
      listaFinal.innerHTML = selecionados.map((a,i) => 
        `<li class="list-group-item d-flex justify-content-between text-black">${a.nome}` +
        `<button class="btn btn-sm btn-danger" onclick="remover(${i})">×</button></li>`
      ).join('');
      document.getElementById('alunosSelecionados').value = selecionados.map(a => a.id).join(',');
    }

    window.remover = i => {
      selecionados.splice(i,1);
      updateLista();
    };

    btnAdd.addEventListener('click', () => {
      // Apenas reusar lógica de clique via listaSug
      const first = listaSug.querySelector('li[data-id]');
      if (first) first.click();
    });

    window.validateForm = () => {
      if (!document.getElementById('ref_numero').value) {
        showAlert('Informe o número de referência.', 'danger'); return false;
      }
      if (!selecionados.length) {
        showAlert('Selecione pelo menos um aluno.', 'danger'); return false;
      }
      return true;
    };
  });
</script>

<?php include '../includes/footer.php'; ?>
