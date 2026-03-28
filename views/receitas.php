<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';


$isAdmin    = $_SESSION['user']['tipo'] === 'admin';
$userId     = $_SESSION['user']['id'];

// Paginação
$porPagina   = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset      = ($paginaAtual - 1) * $porPagina;

// Filtros
$categoriaFiltro = $_GET['categoria']   ?? '';
$dataInicio      = $_GET['data_inicio'] ?? '';
$dataFim         = $_GET['data_fim']    ?? '';
$alunoFiltro     = trim($_GET['aluno_nome'] ?? '');

// Carrega lista de alunos para sugestão
$alunos = $conn->query("SELECT id,nome FROM alunos WHERE ativo=1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// AÇÕES DE CRUD
if (isset($_POST['salvar'])) {
    $id        = $_POST['id']        ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $valor     = floatval($_POST['valor']);
    $data      = $_POST['data']      ?? date('Y-m-d');
    $descricao = trim($_POST['descricao']);
    $alunosIds = array_filter(explode(',', $_POST['alunosSelecionados'] ?? ''), 'strlen');

    if ($categoria && $valor > 0 && $data && count($alunosIds)) {
        if ($id) {
            // Atualiza receita
            $sqlParams = [$categoria, $descricao, $valor, $data, $id];
            $sql       = "UPDATE receitas SET categoria=?,descricao=?,valor=?,data=? WHERE id=?";
            if (!$isAdmin) {
                $sql .= " AND user_id=?"; $sqlParams[] = $userId;
            }
            $stmt = $conn->prepare($sql);
            $stmt->execute($sqlParams);
        } else {
            // Insere nova receita
            $stmt = $conn->prepare(
                "INSERT INTO receitas (categoria,descricao,valor,data,user_id) VALUES (?,?,?,?,?)"
            );
            $stmt->execute([$categoria,$descricao,$valor,$data,$userId]);
            $id = $conn->lastInsertId();
        }
        // Registra transações para cada aluno selecionado
        $logStmt = $conn->prepare(
            "INSERT INTO transacoes (user_id,tipo,descricao,valor,data,aluno_id,categoria) VALUES (?,?,?,?,?,?,?)"
        );
        foreach ($alunosIds as $aid) {
            $logStmt->execute([$userId,'receita',$descricao,$valor,$data,intval($aid),$categoria]);
        }
        header("Location: receitas.php?success=1"); exit;
    } else {
        $erro = "Preencha todos os campos e selecione pelo menos um aluno.";
    }
}

// Exclusão
if (isset($_GET['excluir'])) {
    $idExcluir = intval($_GET['excluir']);
    $sql       = "DELETE FROM receitas WHERE id=?";
    $params    = [$idExcluir];
    if (!$isAdmin) { $sql .= " AND user_id=?"; $params[] = $userId; }
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    header("Location: receitas.php?success=1"); exit;
}

// BUSCA E PAGINAÇÃO
$params    = [];
$queryBase = " FROM receitas r WHERE 1=1";
if (!$isAdmin) { $queryBase .= " AND r.user_id=?"; $params[] = $userId; }
if ($categoriaFiltro) { $queryBase .= " AND r.categoria=?"; $params[] = $categoriaFiltro; }
if ($dataInicio)      { $queryBase .= " AND r.data>=?";     $params[] = $dataInicio; }
if ($dataFim)         { $queryBase .= " AND r.data<=?";     $params[] = $dataFim; }

// Aplica filtro por nome do aluno usando subconsulta
if ($alunoFiltro) {
  $queryBase .= " AND EXISTS (
    SELECT 1 FROM transacoes t
    JOIN alunos a ON a.id = t.aluno_id
    WHERE t.tipo='receita'
      AND t.descricao=r.descricao
      AND t.valor=r.valor
      AND t.data=r.data
      AND t.categoria=r.categoria
      AND a.nome LIKE ?
  )";
  $params[] = "%$alunoFiltro%";
}
// Conta total
$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total".$queryBase);
$stmtTotal->execute($params);
$totalReg = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalReg/$porPagina);

// Lista com nomes dos alunos concatenados
$query = "SELECT r.*, (
    SELECT GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') FROM transacoes t
    JOIN alunos a ON t.aluno_id=a.id
    WHERE t.tipo='receita' AND t.descricao=r.descricao
      AND t.valor=r.valor AND t.data=r.data AND t.categoria=r.categoria
) AS alunos_nomes";
$query .= $queryBase." ORDER BY r.data DESC LIMIT $porPagina OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorias de receita
$tiposReceita = [
    'matricula'=>['Matrícula','bi bi-pencil-square'],
    'prestacao'=>['Prestação','bi bi-cash-coin'],
    'averbamento'=>['Averbamento','bi bi-file-earmark-check'],
    'outros'=>['Outros','bi bi-plus-circle'],
];
?>

<?php include '../includes/header.php'; ?>
<h3><i class="bi bi-cash-stack"></i> Receitas</h3>
<?php if(!empty($_GET['success'])): ?><div class="alert alert-success">Operação realizada com sucesso!</div><?php endif; ?>
<?php if(!empty($erro)): ?><div class="alert alert-danger"><?=htmlspecialchars($erro)?></div><?php endif; ?>

<!-- FILTROS -->
<form method="GET" class="row g-2 mb-3 align-items-end">
  <div class="col-md-3">
    <label>Categoria</label>
    <select name="categoria" class="form-select">
      <option value="">Todas</option>
      <?php foreach($tiposReceita as $k=>$v): ?>
      <option value="<?=$k?>" <?= $categoriaFiltro===$k?'selected':''?>><?=$v[0]?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label>Data Início</label>
    <input type="date" name="data_inicio" value="<?=$dataInicio?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label>Data Fim</label>
    <input type="date" name="data_fim" value="<?=$dataFim?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label>Nome do Aluno</label>
    <input type="text" name="aluno_nome" value="<?=htmlspecialchars($alunoFiltro)?>" class="form-control" placeholder="Ex: João">
  </div>
  <div class="col-md-12 d-flex gap-2 mt-2">
    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel-fill"></i> Filtrar</button>
    <a href="?" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Limpar</a>
    <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalReceita">
      <i class="bi bi-plus-circle"></i> Nova Receita
    </button>
  </div>
</form>


<!-- TABELA -->
<div class="table-responsive shadow-sm rounded">
  <table class="table table-hover align-middle">
    <thead class="table-primary"><tr>
      <th>#</th><th>Data</th><th>Categoria</th><th>Descrição</th>
      <th>Valor (R$)</th><th>Alunos</th><th>Ações</th>
    </tr></thead>
    <tbody>
      <?php if($receitas): $i=1+$offset; foreach($receitas as $r): ?>
      <tr>
        <td><?=$i++?></td>
        <td><?=date('d/m/Y',strtotime($r['data']))?></td>
        <td><?=ucfirst($r['categoria'])?></td>
        <td><?=htmlspecialchars($r['descricao'])?></td>
        <td><?=number_format($r['valor'],2,',','.')?></td>
        <td><?=htmlspecialchars($r['alunos_nomes']?:'-')?></td>
        <td>
          <button class="btn btn-sm btn-info editarReceitaBtn"
            data-id="<?=$r['id']?>"
            data-categoria="<?=$r['categoria']?>"
            data-data="<?=$r['data']?>"
            data-valor="<?=$r['valor']?>"
            data-descricao="<?=htmlspecialchars($r['descricao'])?>"
            data-alunos="<?=htmlspecialchars($r['alunos_nomes'])?>"
          ><i class="fas fa-edit"></i></button>
          <a href="?excluir=<?=$r['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirma exclusão?');"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="7" class="text-center">Nenhuma receita cadastrada.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- PAGINAÇÃO -->
<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for($p=1;$p<=$totalPaginas;$p++): ?>
    <li class="page-item <?= $paginaAtual==$p?'active':''?>">
      <a class="page-link" href="?<?=http_build_query(array_merge($_GET,['pagina'=>$p]))?>"><?=$p?></a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>

<!-- MODAL CADASTRO/EDIÇÃO -->
<div class="modal fade" id="modalReceita" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" onsubmit="return validateForm();">
        <input type="hidden" name="id" id="receitaId">
        <input type="hidden" name="alunosSelecionados" id="alunosSelecionados">
        <div class="modal-header">
          <h5 class="modal-title">Nova Receita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pesquisar Aluno</label>
            <div class="input-group">
              <input type="text" id="searchAluno" class="form-control" placeholder="Nome do aluno...">
              <button type="button" id="btnAdicionar" class="btn btn-success" disabled><i class="fas fa-plus"></i></button>
            </div>
            <ul id="sugestoes" class="list-group mt-1"></ul>
            <ul id="listaFinal" class="list-group mt-2"></ul>
          </div><div class="mb-3">
            <label>Categoria</label>
            <select name="categoria" id="categoria_receita" class="form-select" required>
              <option value="">Selecione</option>
              <?php foreach($tiposReceita as $k=>$v): ?><option value="<?=$k?>"><?=$v[0]?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label>Data</label><input type="date" name="data" id="data_receita" class="form-control" value="<?=date('Y-m-d')?>" required></div>
          <div class="mb-3"><label>Valor</label><input type="number" name="valor" id="valor_receita" step="0.01" class="form-control" required></div>
          <div class="mb-3"><label>Descrição</label><textarea name="descricao" id="descricao_receita" class="form-control"></textarea></div>
          
        </div>
        <div class="modal-footer">
          <button type="submit" name="salvar" class="btn btn-success">Salvar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const searchInput=document.getElementById('searchAluno');
  const listaSug=document.getElementById('sugestoes');
  const btnAdd=document.getElementById('btnAdicionar');
  const listaFinal=document.getElementById('listaFinal');
  let selecionados=[];

  function showAlert(msg,type){
    const div=document.createElement('div'); div.className=`alert alert-${type} alert-dismissible fade show`;
    div.innerHTML=`${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.querySelector('.modal-body').prepend(div);
    setTimeout(()=>div.remove(),2000);
  }

  searchInput.addEventListener('input',()=>{
    const q=searchInput.value.trim(); listaSug.innerHTML=''; btnAdd.disabled=true;
    if(q.length<1){listaSug.innerHTML='<li class="list-group-item text-muted">Digite ao menos 2 caracteres.</li>';return;}
    fetch(`../ajax/busca_aluno.php?q=${encodeURIComponent(q)}`)
      .then(r=>r.ok?r.json():Promise.reject())
      .then(data=>{
        listaSug.innerHTML=data.length
          ?data.map(a=>`<li class="list-group-item list-group-item-action text-black" data-id="${a.id}" data-nome="${a.nome}">${a.nome}</li>`).join('')
          :'<li class="list-group-item text-muted">Nenhum aluno encontrado.</li>';
      }).catch(()=>showAlert('Erro ao buscar alunos.','danger'));
  });

  listaSug.addEventListener('click',e=>{
    const li=e.target.closest('li[data-id]'); if(!li) return;
    const id=li.dataset.id,nome=li.dataset.nome;
    if(selecionados.find(a=>a.id==id)){showAlert('Aluno já selecionado.','warning');return;}
    if(selecionados.length>=10){showAlert('Máximo 10 alunos.','danger');return;}
    selecionados.push({id,nome}); updateLista(); searchInput.value=''; listaSug.innerHTML='';
  });

  btnAdd.addEventListener('click',()=>{ const first=listaSug.querySelector('li[data-id]'); if(first) first.click(); });

  window.remover=i=>{ selecionados.splice(i,1); updateLista(); };

  function updateLista(){
    listaFinal.innerHTML=selecionados.map((a,i)=>`<li class="list-group-item d-flex justify-content-between text-black">${a.nome}<button class="btn btn-sm btn-danger" onclick="remover(${i})">×</button></li>`).join('');
    document.getElementById('alunosSelecionados').value=selecionados.map(a=>a.id).join(',');
    btnAdd.disabled=selecionados.length>=10;
  }

  window.validateForm=()=>{
    if(!document.getElementById('alunosSelecionados').value){showAlert('Selecione ao menos um aluno.','danger');return false;} return true;
  };
});

document.querySelectorAll('.editarReceitaBtn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.getElementById('receitaId').value=btn.dataset.id;
    document.getElementById('categoria_receita').value=btn.dataset.categoria;
    document.getElementById('data_receita').value=btn.dataset.data;
    document.getElementById('valor_receita').value=btn.dataset.valor;
    document.getElementById('descricao_receita').value=btn.dataset.descricao;

    // Novidade: restaurar alunos no modal para edição
    const nomes = btn.dataset.alunos?.split(',') || [];
    const listaFinal = document.getElementById('listaFinal');
    const selecionadosInput = document.getElementById('alunosSelecionados');
    const alunosRestaurados = nomes.map((nome, i) => {
      return { id: 'rest_' + i, nome: nome.trim() };
    });

    window.selecionados = alunosRestaurados;
    listaFinal.innerHTML = alunosRestaurados.map((a,i)=>
      `<li class="list-group-item d-flex justify-content-between">
        ${a.nome}<button class="btn btn-sm btn-danger" onclick="remover(${i})">&times;</button>
      </li>`).join('');
    selecionadosInput.value = ''; // alunos editados não são reusados no backend, então limpar

    new bootstrap.Modal(document.getElementById('modalReceita')).show();
  });
});

</script>

<?php include '../includes/footer.php'; ?>
