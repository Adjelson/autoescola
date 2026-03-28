<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

// Buscar dados de salários e dívidas ordenados alfabeticamente por funcionário
$sql = "
SELECT 
  f.data AS data_emitida, 
  u.nome AS funcionario, 
  f.salario AS valor_salario,
  IFNULL(d.quantidade, 0) AS valor_divida,
  IFNULL(d.descricao, '-') AS descricao_divida
FROM faturas_salarios f
JOIN users u ON f.user_id = u.id
LEFT JOIN dividas d ON d.user_id = f.user_id AND DATE(d.data) = DATE(f.data)
ORDER BY u.nome ASC, f.data DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Montar HTML
$html  = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Relatório de Salários e Dívidas</title>';
$html .= '<style>
  body { margin-top: 100px; margin-left: 40px; margin-right: 40px; font-family: Arial, sans-serif; font-size: 16px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #333; padding: 8px 6px; text-align: left; }
  th { background-color: #f2f2f2; font-weight: bold; }
  .header { text-align: center; margin-bottom: 30px; }
  .footer { text-align: right; font-size: 12px; margin-top: 40px; }
</style>';
$html .= '</head><body>';
$html .= '<div class="header">
  <img src="./../assets/logo.png" width="150" />

<h2>Folha de Salário</h2></div>';
$html .= '<table><thead><tr>';
$html .= '<th style="width: 25%;">Funcionário</th>';
$html .= '<th style="width: 15%;">Data</th>';
$html .= '<th style="width: 20%; text-align: right;">Salário (Dbs)</th>';
$html .= '<th style="width: 20%; text-align: right;">Dívida (Dbs)</th>';
$html .= '<th style="width: 20%; text-align: center;">Assinatura</th>';
$html .= '</tr></thead><tbody>';
foreach ($dados as $d) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($d['funcionario']) . '</td>';
    $html .= '<td>' . date('d/m/Y', strtotime($d['data_emitida'])) . '</td>';
    $html .= '<td style="text-align:right;">' . number_format($d['valor_salario'], 2, ',', '.') . '</td>';
    $html .= '<td style="text-align:right;">' . number_format($d['valor_divida'], 2, ',', '.') . '</td>';
    $html .= '<td style="text-align:center;">_________________________</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$html .= '<div class="footer">
  O Responsável<br><br>
  ____________________________<br>
  Manuel Alves<br>
  Gerado em ' . date('d/m/Y H:i') . ' por Sistema de gestão.
</div>';
$html .= '</body></html>';

// Gerar PDF com mPDF
$mpdf = new Mpdf([
    'orientation' => 'L',
    'default_font' => 'arial',
    'margin_top' => 40,
    'margin_bottom' => 30,
    'margin_left' => 20,
    'margin_right' => 20
]);

$mpdf->WriteHTML($html);
$mpdf->Output('relatorio_salarios_dividas.pdf', \Mpdf\Output\Destination::INLINE);
exit;
