<?php
// models/Escola.php

require_once __DIR__ . '/../config/database.php';

class Escola {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO escolas (nome, nif, email) VALUES (:nome, :nif, :email)"
        );
        $stmt->execute([
            ':nome'  => $data['nome'],
            ':nif'   => $data['nif'],
            ':email' => $data['email'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM escolas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function nifExists(string $nif): bool {
        $stmt = $this->db->prepare("SELECT id FROM escolas WHERE nif = ?");
        $stmt->execute([$nif]);
        return (bool)$stmt->fetch();
    }

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM escolas WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    public function listAll(): array {
        return $this->db->query("SELECT * FROM escolas ORDER BY nome")->fetchAll();
    }
}
