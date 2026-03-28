<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if ($_SESSION['user']['tipo'] !== 'admin') {
  exit('Acesso negado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'];
  $nova_divida = floatval($_POST['divida']);

  $stmt = $conn->prepare("UPDATE salarios SET divida = ? WHERE user_id = ?");
  $stmt->execute([$nova_divida, $user_id]);

  header("Location: salarios.php?updated=1");
  exit;
}
?>
