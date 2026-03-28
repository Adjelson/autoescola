<?php
require '../vendor/autoload.php';
require '../config/db.php';

use Mpdf\Mpdf;

if (!isset($_GET['id'])) exit("Pedido não especificado.");

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM pedidos_exame WHERE id = ? AND tipo = 'normal'");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) exit("Pedido não encontrado.");

$ref = str_pad($pedido['ref_numero'], 3, '0', STR_PAD_LEFT);
$alunos = json_decode($pedido['alunos'], true);

$meses = [
    'January' => 'janeiro',
    'February' => 'fevereiro',
    'March' => 'março',
    'April' => 'abril',
    'May' => 'maio',
    'June' => 'junho',
    'July' => 'julho',
    'August' => 'agosto',
    'September' => 'setembro',
    'October' => 'outubro',
    'November' => 'novembro',
    'December' => 'dezembro'
];

$data = date('d F Y', strtotime($pedido['data_emissao']));
foreach ($meses as $en => $pt) {
    $data = str_replace($en, $pt, $data);
}
$dataExtenso = str_replace(' ', ' de ', $data);

$dataExame = date('d F Y', strtotime($pedido['data_exame']));
foreach ($meses as $en => $pt) {
    $dataExame = str_replace($en, $pt, $dataExame);
}
$dataExameExtenso = str_replace(' ', ' de ', $dataExame);



$html = '
<style>
  @page { margin: 3cm; }
  body { margin-top: 30px; font-family: "Times New Roman", serif; font-size: 14px; line-height: 1.6; }
  .logo { margin-bottom: 20px; }
  .titulo { font-weight: bold; font-size: 16px; margin-bottom: 10px; }
  .conteudo { text-align: justify; margin-top: 5px; }
    .alunos ol { padding-left: 20px; }
  .assinatura { margin-top: 80px; text-align: center; }
  .rodape { margin-top: 80px; font-size: 14px; text-align: center; }
</style>

<div class="logo">
  <img src="./../assets/logo.png" width="150" />
</div>

<div class="titulo">
  Ref.nº ' . $ref . '/ECQ/STP<br>
  <strong>Exame Extra</strong><br><br>
  Assunto: Pedido do Exame Teórico e Prático.
</div>

<div class="conteudo">
  <p>A Escola de Condução de Quilombo vem pela presente expor a Vossa Excelência a lista dos candidatos 
  abaixo mencionados a fim de serem submetidos aos exames teórico e prático a ser realizado no dia
  <strong>' . $dataExameExtenso . '</strong>.
  </p>
</div>

<div class="alunos">
  <p><strong>Nomes dos Candidatos</strong></p>
  <ol>';
foreach ($alunos as $a) {
    $html .= '<li>' . htmlspecialchars($a['nome']) . ' ------------------------- Exame (' . htmlspecialchars($a['categoria']) . ')</li>';
}
$html .= '
  </ol>
</div>

<div class="assinatura">
  O Responsável<br><br>
  ____________________________<br>
  Manuel Alves
</div>

<div class="rodape">
  Escola de Condução Quilombo. Tel.: 9912194 / 9087318<br>
  Emissão: ' . $dataExtenso . '
</div>
';

// Geração do PDF com mPDF
$mpdf = new Mpdf([
    'default_font' => 'times',
    'format' => 'A4',
    'margin_top' => 30,
    'margin_bottom' => 30,
    'margin_left' => 20,
    'margin_right' => 20,
]);

$mpdf->WriteHTML($html);
$mpdf->Output("exame_extra_{$ref}.pdf", \Mpdf\Output\Destination::INLINE);
