<?php
require '../vendor/autoload.php';
require '../config/db.php';

use Mpdf\Mpdf;

if (!isset($_GET['id'])) {
    exit("Pedido não especificado.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM pedidos_licenca WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) exit("Pedido não encontrado.");

$ref = str_pad($pedido['ref_numero'], 3, '0', STR_PAD_LEFT);
$alunos = json_decode($pedido['alunos'], true);

// Traduzir data
$meses = [
    'January' => 'janeiro',  'February' => 'fevereiro', 'March' => 'março',
    'April' => 'abril',      'May' => 'maio',           'June' => 'junho',
    'July' => 'julho',       'August' => 'agosto',      'September' => 'setembro',
    'October' => 'outubro',  'November' => 'novembro',  'December' => 'dezembro'
];

$data = date('d F Y', strtotime($pedido['data_emissao']));
foreach ($meses as $en => $pt) {
    $data = str_replace($en, $pt, $data);
}
$dataExtenso = str_replace(' ', ' de ', $data);

// HTML para o PDF
$html = '
<style>
  @page { margin: 3cm; }
  body { font-family: "Times New Roman", Times, serif; font-size: 14px; }
  .logo { margin-bottom: 20px; }
  .titulo { margin-top: 20px; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
  .conteudo { text-align: justify; font-size: 14px; }
  .alunos ol { padding-left: 30px; font-size: 14px; }
  .assinatura { margin-top: 50px; text-align: center; }
  .rodape { margin-top: 5px; text-align: center; font-size: 14px; }
</style>

<div class="logo">
  <img src="./../assets/logo.png" width="150" />
</div>

<div class="titulo">
  Ref. nº ' . $ref . '/ECQ/STP<br><br>
  Assunto: Pedido de licença de aprendizagem
</div>

<div class="conteudo">
  <p>
    A Escola de Condução Quilombo vem pela presente expor à Vossa Excelência a lista dos alunos para pedido de licença de aprendizagem.
  </p>

  <p><strong>Nome dos candidatos:</strong></p>
  <ol>';
foreach ($alunos as $a) {
    $html .= "<li>" . htmlspecialchars($a) . "</li>";
}
$html .= '
  </ol>
</div>

<div class="assinatura">
  O Responsável<br><br>
  ____________________________<br><br>
  Manuel Alves
</div>

<div class="rodape">
  Escola de Condução Quilombo, Tel.: 9912194 / 9087318<br>
  Emissão: ' . $dataExtenso . '
</div>
';

// Instância do mPDF
$mpdf = new Mpdf([
    'default_font' => 'times',
    'format' => 'A4',
    'margin_top' => 20,
    'margin_bottom' => 30,
    'margin_left' => 20,
    'margin_right' => 20,
]);

$mpdf->WriteHTML($html);
$mpdf->Output("pedido_licenca_{$ref}.pdf", \Mpdf\Output\Destination::INLINE);
