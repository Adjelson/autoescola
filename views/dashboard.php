<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';
include '../includes/header.php';

if ($_SESSION['user']['tipo'] !== 'admin') {
  exit;
}

try {
  // Totais
  $totalReceitas = (float) $conn->query("SELECT COALESCE(SUM(valor),0) FROM receitas")->fetchColumn();
  $totalDespesas = (float) $conn->query("SELECT COALESCE(SUM(valor),0) FROM despesas")->fetchColumn();
  $lucro         = $totalReceitas - $totalDespesas;

  // Usuários e alunos
  $totalUsers            = (int) $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
  $totalAlunosCurso      = (int) $conn->query("SELECT COUNT(*) FROM alunos WHERE status='Em curso'")->fetchColumn();
  $totalAlunosFinalizado = (int) $conn->query("SELECT COUNT(*) FROM alunos WHERE status='Finalizado'")->fetchColumn();

  // Dados por mês (últimos 12 meses)
  $sqlMes = "
    SELECT DATE_FORMAT(data,'%Y-%m') AS mes,
           SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END) AS rec,
           SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END) AS desp
    FROM (
      SELECT data,valor,'receita' AS tipo FROM receitas
      UNION ALL
      SELECT data,valor,'despesa' AS tipo FROM despesas
    ) t
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY mes
    ORDER BY mes
  ";
  $porMes = $conn->query($sqlMes)->fetchAll(PDO::FETCH_ASSOC);

  // Dados por dia (últimos 30 dias)
  $sqlDia = "
    SELECT DATE_FORMAT(data,'%Y-%m-%d') AS dia,
           SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END) AS rec,
           SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END) AS desp
    FROM (
      SELECT data,valor,'receita' AS tipo FROM receitas
      UNION ALL
      SELECT data,valor,'despesa' AS tipo FROM despesas
    ) t
    WHERE data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY dia
    ORDER BY dia
  ";
  $porDia = $conn->query($sqlDia)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  echo "<div class='alert alert-danger'>Erro ao carregar dados.</div>";
  exit;
}

// Prepara para JS
$meses   = array_column($porMes, 'mes');
$recMes  = array_column($porMes, 'rec');
$despMes = array_column($porMes, 'desp');
$dias    = array_column($porDia, 'dia');
$recDia  = array_column($porDia, 'rec');
$despDia = array_column($porDia, 'desp');
?>

<style>
.card-mini {
  min-height: 100px;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,.1);
}
</style>

<h4 class="mb-4">Dashboard</h4>

<!-- CARDS -->
<div class="row g-3 mb-5">
  <?php
  function card($bg, $text, $label, $value) {
    $fmt = is_numeric($value)
      ? number_format($value,2,',','.')
      : $value;
    echo "
      <div class='col-12 col-md-4 col-xl-2'>
        <div class='card bg-{$bg}-subtle card-mini'>
          <div class='text-{$text} fw-semibold mb-2'>{$label}</div>
          <h5>{$fmt}</h5>
        </div>
      </div>
    ";
  }
  card('success','success','Receitas (Kz)',$totalReceitas);
  card('danger', 'danger', 'Despesas (Kz)',$totalDespesas);
  card('warning','warning','Lucro (Kz)',$lucro);
  card('primary','primary','Utilizadores',$totalUsers);
  card('info','info','Alunos em curso',$totalAlunosCurso);
  card('secondary','secondary','Alunos finalizados',$totalAlunosFinalizado);
  ?>
</div>

<!-- GRÁFICOS -->
<div class="row">
  <div class="col-md-6 mb-4">
    <h5>Por Mês (Últimos 12 meses)</h5>
    <canvas id="chartMes"></canvas>
  </div>
  <div class="col-md-6 mb-4">
    <h5>Por Dia (Últimos 30 dias)</h5>
    <canvas id="chartDia"></canvas>
  </div>
</div>

<script src="./../assets/js/chart.js/dist/chart.umd.js"></script>
<script>
// PHP → JS
const meses   = <?= json_encode($meses) ?>;
const recMes  = <?= json_encode($recMes) ?>;
const despMes = <?= json_encode($despMes) ?>;
const dias    = <?= json_encode($dias) ?>;
const recDia  = <?= json_encode($recDia) ?>;
const despDia = <?= json_encode($despDia) ?>;

// Gráfico Mensal
new Chart(document.getElementById('chartMes'), {
  type: 'line',
  data: {
    labels: meses,
    datasets: [
      { label: 'Receita', data: recMes, fill: false },
      { label: 'Despesa', data: despMes, fill: false }
    ]
  },
  options: {
    plugins: { legend: { position: 'bottom' } },
    scales: { y: { beginAtZero: true } }
  }
});

// Gráfico Diário
new Chart(document.getElementById('chartDia'), {
  type: 'line',
  data: {
    labels: dias,
    datasets: [
      { label: 'Receita', data: recDia, fill: false },
      { label: 'Despesa', data: despDia, fill: false }
    ]
  },
  options: {
    plugins: { legend: { position: 'bottom' } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

<?php include '../includes/footer.php'; ?>
