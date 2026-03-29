<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, e.nome as escola_nome
             FROM utilizadores u
             LEFT JOIN escolas e ON e.id = u.escola_id
             WHERE u.email = ? AND u.ativo = 1 LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $user['permissoes'] = $user['permissoes']
                ? (json_decode($user['permissoes'], true) ?? [])
                : [];
        }
        return $user ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, e.nome as escola_nome
             FROM utilizadores u
             LEFT JOIN escolas e ON e.id = u.escola_id
             WHERE u.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $user['permissoes'] = $user['permissoes']
                ? (json_decode($user['permissoes'], true) ?? [])
                : [];
        }
        return $user ?: null;
    }

    public function create(array $data): int {
        $permissoes = isset($data['permissoes']) ? json_encode($data['permissoes']) : null;
        $stmt = $this->db->prepare(
            "INSERT INTO utilizadores (nome, email, password, role, escola_id, ativo, permissoes)
             VALUES (:nome, :email, :password, :role, :escola_id, 1, :permissoes)"
        );
        $stmt->execute([
            ':nome'       => $data['nome'],
            ':email'      => $data['email'],
            ':password'   => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':role'       => $data['role'],
            ':escola_id'  => $data['escola_id'] ?? null,
            ':permissoes' => $permissoes,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePermissoes(int $id, int $escola_id, array $permissoes): bool {
        $stmt = $this->db->prepare(
            "UPDATE utilizadores SET permissoes = ?
             WHERE id = ? AND escola_id = ? AND role = 'funcionario'"
        );
        $stmt->execute([json_encode($permissoes), $id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function updatePassword(int $id, string $newPassword): void {
        $stmt = $this->db->prepare(
            "UPDATE utilizadores SET password = ? WHERE id = ?"
        );
        $stmt->execute([
            password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
            $id,
        ]);
    }

    public function listBySchool(int $escola_id): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM utilizadores WHERE escola_id = ? ORDER BY role, nome"
        );
        $stmt->execute([$escola_id]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$u) {
            $u['permissoes'] = $u['permissoes']
                ? (json_decode($u['permissoes'], true) ?? [])
                : [];
        }
        return $rows;
    }

    public function toggleAtivo(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "UPDATE utilizadores SET ativo = !ativo
             WHERE id = ? AND escola_id = ? AND role != 'admin_escola'"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool {
        $sql    = "SELECT id FROM utilizadores WHERE email = ?";
        $params = [$email];
        if ($excludeId) {
            $sql    .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }
}
