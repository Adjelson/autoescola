<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$tipoUser   = $_SESSION['user']['tipo'];
$userId     = $_SESSION['user']['id'];
$tipo       = $_GET['tipo'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim    = $_GET['data_fim'] ?? '';

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

if ($tipo && in_array($tipo, ['receita', 'despesa'])) {
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

$query .= " ORDER BY t.data DESC, t.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totais = [];
foreach ($transacoes as $t) {
  $label = ucfirst($t['tipo']);
  $totais[$label] = ($totais[$label] ?? 0) + $t['valor'];
}
$totalGeral = array_sum($totais);

$options = (new Options())->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

ob_start();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <title>Relatório de Transações</title>
  <style>
    body {
      font-family: Arial;
      font-size: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 6px;
      text-align: left;
    }

    h2 {
      text-align: center;
    }

    .assinatura {
      margin-top: 50px;
    }

    .legend {
      font-size: 15px;
      text-align: center;
    }

    .legend span {
      display: inline-block;
      width: 12px;
      height: 12px;
      margin-right: 5px;
    }

    .footer {
      font-size: 12px;
      text-align: center;
      margin-top: 40px;
      color: #555;
    }
  </style>
</head>

<body>
  <div>
    <h1>Relatório de Transações</h1>
    <p><strong>Tipo:</strong> <?= $tipo ?: 'Tudo' ?></p>
    <p><strong>Período:</strong> <?= $dataInicio ?: '—' ?> a <?= $dataFim ?: '—' ?></p>
  </div>
<img src="http://localhost/nova/assets/logo.png" alt="">
  <table>
    <thead>
      <tr>
        <th>Data</th>
        <th>Tipo</th>
        <th>Aluno</th>
        <th>Descrição</th>
        <th>Valor (R$)</th>
        <th>Utilizador</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($transacoes as $t): ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($t['data'])) ?></td>
          <td><?= ucfirst($t['tipo']) ?></td>
          <td><?= htmlspecialchars($t['aluno_nome'] ?? '-') ?></td>
          <td><?= htmlspecialchars($t['descricao']) ?></td>
          <td><?= number_format($t['valor'], 2, ',', '.') ?></td>
          <td><?= htmlspecialchars($t['user_nome'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($transacoes)): ?>
        <tr>
          <td colspan="6" style="text-align:center;">Nenhuma transação encontrada.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="legend">
    <?php foreach ($totais as $label => $valor):
      $pct = $totalGeral ? round(($valor / $totalGeral) * 100, 1) : 0;
      $color = $label === 'Receita' ? '#00ff33ff' : '#dc3545';
    ?>
      <div><span style="background:<?= $color ?>"></span><?= $label ?>: R$ <?= number_format($valor, 2, ',', '.') ?> (<?= $pct ?>%)</div>
    <?php endforeach; ?>
  </div>

  <div class="footer">
    <p>&copy; <?= date('Y') ?> Gestão Financeira — Relatório gerado em <?= date('d/m/Y H:i') ?></p>
  </div>
</body>

</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('relatorio_transacoes.pdf', ['Attachment' => false]);
exit;
