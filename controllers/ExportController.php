<?php
// controllers/ExportController.php
// Excel → SpreadsheetML (nativo, sem bibliotecas)
// PDF   → HTML imprimível entregue como download via data URI trigger

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Receita.php';
require_once __DIR__ . '/../models/Despesa.php';
require_once __DIR__ . '/../models/Aluno.php';
require_once __DIR__ . '/../config/permissoes.php';
require_once __DIR__ . '/../middleware/auth.php';

class ExportController {

    // -------------------------------------------------------
    // EXCEL helpers (SpreadsheetML — abre no Excel/LibreOffice)
    // -------------------------------------------------------

    private function xlsxStart(string $filename): void {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
               xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
<Styles>
  <Style ss:ID="header"><Font ss:Bold="1"/><Interior ss:Color="#16a34a" ss:Pattern="Solid"/><Font ss:Bold="1" ss:Color="#FFFFFF"/></Style>
  <Style ss:ID="total"><Font ss:Bold="1"/><Interior ss:Color="#f0fdf4" ss:Pattern="Solid"/></Style>
  <Style ss:ID="money"><NumberFormat ss:Format=\'#,##0.00\ "€"\'/></Style>
  <Style ss:ID="date"><NumberFormat ss:Format="DD/MM/YYYY"/></Style>
</Styles>
<Worksheet ss:Name="Dados"><Table>';
    }

    private function xlsxRow(array $cells, string $style = ''): void {
        echo '<Row>';
        foreach ($cells as $cell) {
            $type = 'String';
            $s    = $style ? " ss:StyleID=\"$style\"" : '';
            if (is_numeric($cell) && $cell !== '') {
                $type = 'Number';
            }
            $val = htmlspecialchars((string)$cell, ENT_XML1, 'UTF-8');
            echo "<Cell$s><Data ss:Type=\"$type\">$val</Data></Cell>";
        }
        echo '</Row>' . "\n";
    }

    private function xlsxEnd(): void {
        echo '</Table></Worksheet></Workbook>';
        exit;
    }

    // -------------------------------------------------------
    // PDF helpers (pure PHP — gera HTML optimizado para print,
    // depois JavaScript faz window.print() automaticamente)
    // -------------------------------------------------------

    private function pdfPage(string $title, string $tableHtml, array $meta = []): void {
        $escola = e(currentUser()['escola_nome'] ?? 'AutoEscola Financeiro');
        $data   = date('d/m/Y H:i');
        $metaHtml = '';
        foreach ($meta as $label => $val) {
            $metaHtml .= "<span><strong>$label:</strong> $val</span> &nbsp;";
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
<!DOCTYPE html><html lang="pt"><head>
<meta charset="UTF-8">
<title>$title</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Arial,sans-serif;font-size:11px;color:#111;background:#fff;padding:16px}
  .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #16a34a}
  .header h1{font-size:16px;color:#16a34a;margin-bottom:2px}
  .header .meta{font-size:10px;color:#555}
  .header .right{text-align:right;font-size:10px;color:#555}
  table{width:100%;border-collapse:collapse;margin-top:8px}
  th{background:#16a34a;color:#fff;padding:5px 7px;text-align:left;font-size:10px}
  td{padding:4px 7px;border-bottom:1px solid #e5e7eb;font-size:10px}
  tr:nth-child(even) td{background:#f9fafb}
  .tfoot td{background:#f0fdf4;font-weight:bold;border-top:2px solid #16a34a}
  .text-end{text-align:right}
  .text-success{color:#16a34a}
  .text-danger{color:#dc2626}
  @media print{body{padding:0}button{display:none}}
</style>
</head><body>
<div class="header">
  <div>
    <h1>$title</h1>
    <div class="meta">$metaHtml</div>
  </div>
  <div class="right">$escola<br>Gerado: $data</div>
</div>
$tableHtml
<script>window.onload=function(){window.print()}</script>
</body></html>
HTML;
        exit;
    }

    // -------------------------------------------------------
    // RECEITAS
    // -------------------------------------------------------

    public function receitasExcel(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_excel')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        $escola_id = escolarId();
        $mes       = $_GET['mes'] ?? '';
        $receitas  = (new Receita())->list($escola_id, $mes ? ['mes'=>$mes] : []);

        $filename = 'receitas' . ($mes ? '_'.$mes : '') . '_' . date('Ymd') . '.xls';
        $this->xlsxStart($filename);
        $this->xlsxRow(['ID','Data','Aluno','Tipo','Método','Valor (€)','Descrição'], 'header');

        $total = 0;
        foreach ($receitas as $r) {
            $this->xlsxRow([
                $r['id'],
                date('d/m/Y', strtotime($r['data'])),
                $r['aluno_nome'] ?? '—',
                TIPOS_RECEITA[$r['tipo']] ?? $r['tipo'],
                $this->metodoLabel($r['metodo']),
                number_format((float)$r['valor'], 2, ',', '.'),
                $r['descricao'] ?? '',
            ]);
            $total += (float)$r['valor'];
        }
        $this->xlsxRow(['','','','','Total', number_format($total, 2, ',', '.'), ''], 'total');
        $this->xlsxEnd();
    }

    public function receitasPdf(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_pdf')) { flash('danger','Sem permissão.'); redirect('index.php?page=receitas'); }
        $escola_id = escolarId();
        $mes       = $_GET['mes'] ?? '';
        $receitas  = (new Receita())->list($escola_id, $mes ? ['mes'=>$mes] : []);
        $total     = array_sum(array_column($receitas, 'valor'));
        $mesLabel  = $mes ? $this->mesLabel($mes) : 'Todos os meses';

        $rows = '';
        foreach ($receitas as $r) {
            $rows .= '<tr>
              <td>'.date('d/m/Y', strtotime($r['data'])).'</td>
              <td>'.htmlspecialchars($r['aluno_nome'] ?? '—', ENT_QUOTES).'</td>
              <td>'.(TIPOS_RECEITA[$r['tipo']] ?? $r['tipo']).'</td>
              <td>'.$this->metodoLabel($r['metodo']).'</td>
              <td class="text-end text-success">'.money($r['valor']).'</td>
              <td>'.htmlspecialchars($r['descricao'] ?? '', ENT_QUOTES).'</td>
            </tr>';
        }

        $table = '<table>
          <thead><tr><th>Data</th><th>Aluno</th><th>Tipo</th><th>Método</th><th class="text-end">Valor</th><th>Descrição</th></tr></thead>
          <tbody>'.$rows.'</tbody>
          <tfoot><tr class="tfoot"><td colspan="4" class="text-end">Total</td>
            <td class="text-end text-success">'.money($total).'</td><td></td></tr></tfoot>
        </table>';

        $this->pdfPage('Receitas — '.$mesLabel, $table, ['Mês' => $mesLabel, 'Registos' => count($receitas)]);
    }

    // -------------------------------------------------------
    // DESPESAS
    // -------------------------------------------------------

    public function despesasExcel(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_excel')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        $escola_id = escolarId();
        $mes       = $_GET['mes'] ?? '';
        $despesas  = (new Despesa())->list($escola_id, $mes ? ['mes'=>$mes] : []);

        $filename = 'despesas' . ($mes ? '_'.$mes : '') . '_' . date('Ymd') . '.xls';
        $this->xlsxStart($filename);
        $this->xlsxRow(['ID','Data','Categoria','Valor (€)','Descrição'], 'header');

        $total = 0;
        foreach ($despesas as $d) {
            $this->xlsxRow([
                $d['id'],
                date('d/m/Y', strtotime($d['data'])),
                CATEGORIAS_DESPESA[$d['categoria']] ?? $d['categoria'],
                number_format((float)$d['valor'], 2, ',', '.'),
                $d['descricao'],
            ]);
            $total += (float)$d['valor'];
        }
        $this->xlsxRow(['','','Total', number_format($total, 2, ',', '.'), ''], 'total');
        $this->xlsxEnd();
    }

    public function despesasPdf(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_pdf')) { flash('danger','Sem permissão.'); redirect('index.php?page=despesas'); }
        $escola_id = escolarId();
        $mes       = $_GET['mes'] ?? '';
        $despesas  = (new Despesa())->list($escola_id, $mes ? ['mes'=>$mes] : []);
        $total     = array_sum(array_column($despesas, 'valor'));
        $mesLabel  = $mes ? $this->mesLabel($mes) : 'Todos os meses';

        $rows = '';
        foreach ($despesas as $d) {
            $rows .= '<tr>
              <td>'.date('d/m/Y', strtotime($d['data'])).'</td>
              <td>'.(CATEGORIAS_DESPESA[$d['categoria']] ?? $d['categoria']).'</td>
              <td class="text-end text-danger">'.money($d['valor']).'</td>
              <td>'.htmlspecialchars($d['descricao'], ENT_QUOTES).'</td>
            </tr>';
        }

        $table = '<table>
          <thead><tr><th>Data</th><th>Categoria</th><th class="text-end">Valor</th><th>Descrição</th></tr></thead>
          <tbody>'.$rows.'</tbody>
          <tfoot><tr class="tfoot"><td colspan="2" class="text-end">Total</td>
            <td class="text-end text-danger">'.money($total).'</td><td></td></tr></tfoot>
        </table>';

        $this->pdfPage('Despesas — '.$mesLabel, $table, ['Mês' => $mesLabel, 'Registos' => count($despesas)]);
    }

    // -------------------------------------------------------
    // ALUNOS
    // -------------------------------------------------------

    public function alunosExcel(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_excel')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        $escola_id = escolarId();
        $alunos    = (new Aluno())->listBySchool($escola_id);

        $this->xlsxStart('alunos_' . date('Ymd') . '.xls');
        $this->xlsxRow(['ID','Nome','Pacote','Preço Total (€)','Pago (€)','Dívida (€)'], 'header');

        foreach ($alunos as $a) {
            $divida = max(0, (float)$a['preco_total'] - (float)$a['pago_total']);
            $this->xlsxRow([
                $a['id'], $a['nome'], $a['pacote'],
                number_format((float)$a['preco_total'], 2, ',', '.'),
                number_format((float)$a['pago_total'],  2, ',', '.'),
                number_format($divida, 2, ',', '.'),
            ]);
        }
        $this->xlsxEnd();
    }

    public function alunosPdf(): void {
        requireLogin();
        requireEscolaContext();
        if (!temPermissao('exportar_pdf')) { flash('danger','Sem permissão.'); redirect('index.php?page=alunos'); }
        $escola_id = escolarId();
        $alunos    = (new Aluno())->listBySchool($escola_id);

        $rows = '';
        foreach ($alunos as $a) {
            $divida = max(0, (float)$a['preco_total'] - (float)$a['pago_total']);
            $dc     = $divida > 0 ? 'text-danger' : 'text-success';
            $rows  .= '<tr>
              <td>'.htmlspecialchars($a['nome'], ENT_QUOTES).'</td>
              <td>'.htmlspecialchars($a['pacote'], ENT_QUOTES).'</td>
              <td class="text-end">'.money($a['preco_total']).'</td>
              <td class="text-end text-success">'.money($a['pago_total']).'</td>
              <td class="text-end '.$dc.'">'.money($divida).'</td>
            </tr>';
        }

        $totalDivida = array_sum(array_map(fn($a) => max(0,(float)$a['preco_total']-(float)$a['pago_total']), $alunos));
        $table = '<table>
          <thead><tr><th>Nome</th><th>Pacote</th><th class="text-end">Total</th><th class="text-end">Pago</th><th class="text-end">Dívida</th></tr></thead>
          <tbody>'.$rows.'</tbody>
          <tfoot><tr class="tfoot"><td colspan="4" class="text-end">Total em dívida</td>
            <td class="text-end text-danger">'.money($totalDivida).'</td></tr></tfoot>
        </table>';

        $comDivida = count(array_filter($alunos, fn($a) => (float)$a['divida'] > 0));
        $this->pdfPage('Lista de Alunos', $table, ['Total' => count($alunos).' alunos', 'Com dívida' => $comDivida]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function metodoLabel(string $m): string {
        return ['numerario'=>'Numerário','transferencia'=>'Transferência','mbway'=>'MBWay','multibanco'=>'Multibanco'][$m] ?? $m;
    }

    private function mesLabel(string $ym): string {
        [$y, $mo] = explode('-', $ym);
        $nomes = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        return ($nomes[(int)$mo] ?? $mo) . ' ' . $y;
    }
}
