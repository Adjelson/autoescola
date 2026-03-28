<?php
require '../config/db.php';
$id = $_GET['id'] ?? null;
if (!$id) exit("ID não fornecido.");

$stmt = $conn->prepare("DELETE FROM pedidos_licenca WHERE id = ?");
$stmt->execute([$id]);

header('Location: pedido_licenca.php');
