<?php
$host = 'localhost';
$db   = 'gestao_financeira';
$user = 'root';
$pass = ''; // ou 'root' se estiver no Mac
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na ligação: " . $e->getMessage());
}
?>
