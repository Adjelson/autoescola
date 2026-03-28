<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if (!isset($_GET['id'])) {
  exit("ID não especificado.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM pedidos_exame WHERE id = ?");
$stmt->execute([$id]);

header("Location: pedido_exame.php");
exit;
