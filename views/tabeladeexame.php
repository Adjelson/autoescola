<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $dataExame = $_POST['data_exame'] ?? '';
  $nomeAluno = trim($_POST['nome_aluno'] ?? '');
  $multimedia = trim($_POST['multimedia'] ?? '');
  $tipoExame = $_POST['tipo_exame'] ?? '';
  $categoriaCarta = $_POST['categoria_carta'] ?? '';
  $numeroCarta = trim($_POST['numero_carta'] ?? '');

  if ($dataExame && $nomeAluno && $tipoExame && $categoriaCarta) {
    $stmt = $conn->prepare("INSERT INTO pedidos_exame (ref_numero, tipo, data_emissao, data_exame, assunto, alunos)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      rand(1000, 9999),
      'normal',
      date('Y-m-d'),
      $dataExame,
      "$tipoExame ($multimedia)",
      json_encode([$nomeAluno])
    ]);
    $sucesso = "Exame registrado com sucesso.";
  } else {
    $erro = "Preencha todos os campos obrigatórios.";
  }
}

$exames = $conn->query("SELECT * FROM pedidos_exame ORDER BY data_exame DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-4">
  <h3><i class="bi bi-clipboard-check"></i> Registrar Exame</h3>

  <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
  <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalExame">
    <i class="bi bi-plus-circle"></i> Novo Exame
  </button>

  <!-- Modal -->
  <div class="modal fade" id="modalExame" tabindex="-1" aria-labelledby="modalExameLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Novo Exame</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" >
            <div class="mb-3">
              <label for="nome_aluno" class="form-label">Nome do Aluno</label>
              <input type="text" name="nome_aluno" id="nome_aluno" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="data_exame" class="form-label">Data do Exame</label>
              <input type="date" name="data_exame" id="data_exame" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="multimedia" class="form-label">Multimédia</label>
              <select name="multimedia" id="multimedia" class="form-select" required>
                <option value="multimedia">Multimédia</option>
                <option value="oral">Oral</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="tipo_exame" class="form-label">Tipo de Exame</label>
              <select name="tipo_exame" id="tipo_exame" class="form-select" required>
                <option value="Teórico">Teórico</option>
                <option value="Prático">Prático</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="categoria_carta" class="form-label">Categoria da Carta</label>
              <select name="categoria_carta" id="categoria_carta" class="form-select" required>
                <option value="">Nenhum</option>
                <option value="Ligeiro">Ligeiro</option>
                <option value="Pesado">Pesado</option>
                <option value="Motociclo">Motociclo</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="numero_carta" class="form-label">Número da Carta (opcional)</label>
              <input type="text" name="numero_carta" id="numero_carta" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabela de exames -->
  <div class="table-responsive border shadow-sm rounded mt-4">
    <table class="table table-striped">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>Data do Exame</th>
          <th>Tipo</th>
          <th>Assunto</th>
          <th>Alunos</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($exames): foreach ($exames as $i => $ex): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= date('d/m/Y', strtotime($ex['data_exame'])) ?></td>
            <td><?= ucfirst($ex['tipo']) ?></td>
            <td><?= htmlspecialchars($ex['assunto']) ?></td>
            <td>
              <ul class="mb-0">
                <?php foreach (json_decode($ex['alunos'], true) as $aluno): ?>
                  <li><?= htmlspecialchars($aluno) ?></li>
                <?php endforeach; ?>
              </ul>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="text-center">Nenhum exame encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
