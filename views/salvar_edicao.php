<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $ref = intval($_POST['ref_numero']);
  $alunosIds = explode(',', $_POST['alunos'] ?? '');

  if (empty($alunosIds)) {
    exit("Nenhum aluno selecionado.");
  }

  $in = str_repeat('?,', count($alunosIds) - 1) . '?';
  $stmt = $conn->prepare("SELECT nome FROM alunos WHERE id IN ($in)");
  $stmt->execute($alunosIds);
  $nomes = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $stmt = $conn->prepare("UPDATE pedidos_licenca SET ref_numero = ?, alunos = ? WHERE id = ?");
  $stmt->execute([$ref, json_encode($nomes), $id]);

  header("Location: pedido_licenca.php");
  exit;
}
?>
