<?php
require '../vendor/autoload.php';
require '../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) exit("Pedido não especificado.");

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM pedidos_exame WHERE id = ? AND tipo = 'normal'");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) exit("Pedido não encontrado.");

$ref = str_pad($pedido['ref_numero'], 3, '0', STR_PAD_LEFT);
$alunos = json_decode($pedido['alunos'], true);

$meses = [
    'January' => 'janeiro', 'February' => 'fevereiro', 'March' => 'março',
    'April' => 'abril', 'May' => 'maio', 'June' => 'junho',
    'July' => 'julho', 'August' => 'agosto', 'September' => 'setembro',
    'October' => 'outubro', 'November' => 'novembro', 'December' => 'dezembro'
];

function formatarDataExtenso($data, $meses) {
  $data = date('d F Y', strtotime($data));
  foreach ($meses as $en => $pt) $data = str_replace($en, $pt, $data);
  return str_replace(' ', ' de ', $data);
}

$dataExameExtenso = formatarDataExtenso($pedido['data_exame'], $meses);
$dataEmissaoExtenso = formatarDataExtenso($pedido['data_emissao'], $meses);

$logo = realpath(__DIR__ . '/../logo.png');
$img = file_exists($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : '';

$html = '<style>
@page { margin: 2cm; }
body { font-family: "Times New Roman", serif; font-size: 13px; }
.header { text-align: center; margin-bottom: 10px; }
.titulo { font-size: 16px; font-weight: bold; margin-bottom: 6px; text-align: center; }
.subtitulo { font-size: 14px; text-align: center; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #000; padding: 4px; text-align: center; }
.footer { text-align: center; margin-top: 40px; font-size: 13px; }
.assinatura { margin-top: 80px; text-align: left; font-size: 14px; }
</style>';

$html .= '<div class="header">
  <img src="'.$img.'" width="100"><br>
  <strong>REPÚBLICA DEMOCRÁTICA DE SÃO TOMÉ E PRÍNCIPE</strong><br>
  ESCOLA DE CONDUÇÃO QUILOMBO<br>
  (UNIDADE – DISCIPLINA – TRABALHO)
</div>

<div class="titulo">Lista do candidato aos exames (Teórico -se tiver) e Prático de (ligeiro, pesado profissional ou Motociclo)</div>
<div class="subtitulo">A realizar-se na data marcada: <strong>'.$dataExameExtenso.'</strong></div>

<table>
  <thead>
    <tr>
      <th>Nº</th>
      <th>Nome do Candidato</th>
      <th>Nº Carta</th>
      <th>Exame</th>
      <th>Prova a Prestar<br>(Teórico)</th>
      <th>Resultado</th>
      <th>Prova a Prestar<br>(Prático)</th>
      <th>Resultado</th>
      <th>Vogal</th>
      <th>Assinatura</th>
      <th>Examinador</th>
      <th>Directora</th>
    </tr>
  </thead>
  <tbody>';

foreach ($alunos as $i => $a) {
  $nome = htmlspecialchars($a['nome']);
  $tipoExame = $a['tipo_exame'];
  $modo = isset($a['modo_teorico']) ? strtoupper(substr($a['modo_teorico'], 0, 1)) : 'XXXXXXXX';
  $carta = trim($a['numero_carta']) ?: 'XXXXXXXXXX';

  $provaTeorica = (str_contains($tipoExame, 'Teórico')) ? $modo : 'XXXXXXXX';
  $provaPratica = (str_contains($tipoExame, 'Prático')) ? $tipoExame : 'XXXXXXXX';

  $html .= '<tr>
    <td>'.($i+1).'</td>
    <td>'.$nome.'</td>
    <td>'.$carta.'</td>
    <td>'.$tipoExame.'</td>
    <td>'.$provaTeorica.'</td>
    <td></td>
    <td>'.$provaPratica.'</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>';
}

$html .= '</tbody></table>

<div class="assinatura">
  São Tomé, '.$dataEmissaoExtenso.'<br><br>
  A Chefe de Exame<br><br>
  ____________________________<br>
  Manuel Alves
</div>

<div class="footer">
  Escola de Condução Quilombo. Tel.: 9912194 / 9087318
</div>';

$options = new Options();
$options->set('defaultFont', 'Times');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("exame_tabela_{$ref}.pdf", ["Attachment" => false]);
