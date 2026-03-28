<?php
require '../config/db.php';

$q = $_GET['q'] ?? '';
if (!$q) exit('[]');

$stmt = $conn->prepare("SELECT id, nome, categoria FROM alunos WHERE nome LIKE ? LIMIT 10");
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
