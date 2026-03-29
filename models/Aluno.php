<?php
// models/Aluno.php

require_once __DIR__ . '/../config/database.php';

class Aluno {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function listBySchool(int $escola_id, string $search = ''): array {
        $sql = "SELECT a.*,
                    COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS pago_total,
                    a.preco_total - COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS divida
                FROM alunos a WHERE a.escola_id = ?";
        $params = [$escola_id];
        if ($search !== '') {
            $sql .= " AND (a.nome LIKE ? OR a.pacote LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY a.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $escola_id): ?array {
        $stmt = $this->db->prepare(
            "SELECT a.*,
                COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS pago_total,
                a.preco_total - COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS divida
             FROM alunos a WHERE a.id = ? AND a.escola_id = ?"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO alunos (escola_id, nome, pacote, preco_total)
             VALUES (:escola_id, :nome, :pacote, :preco_total)"
        );
        $stmt->execute([
            ':escola_id'   => $data['escola_id'],
            ':nome'        => $data['nome'],
            ':pacote'      => $data['pacote'],
            ':preco_total' => $data['preco_total'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $escola_id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE alunos SET nome=:nome, pacote=:pacote, preco_total=:preco_total
             WHERE id=:id AND escola_id=:escola_id"
        );
        $stmt->execute([
            ':nome'        => $data['nome'],
            ':pacote'      => $data['pacote'],
            ':preco_total' => $data['preco_total'],
            ':id'          => $id,
            ':escola_id'   => $escola_id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare("DELETE FROM alunos WHERE id = ? AND escola_id = ?");
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function countWithDebt(int $escola_id): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM alunos a
             WHERE a.escola_id = ?
             AND a.preco_total > COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0)"
        );
        $stmt->execute([$escola_id]);
        return (int)$stmt->fetchColumn();
    }

    public function listWithDebt(int $escola_id): array {
        $stmt = $this->db->prepare(
            "SELECT a.*,
                COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS pago_total,
                a.preco_total - COALESCE((SELECT SUM(r.valor) FROM receitas r WHERE r.aluno_id = a.id AND r.eliminado = 0), 0) AS divida
             FROM alunos a
             WHERE a.escola_id = ?
             HAVING divida > 0
             ORDER BY divida DESC"
        );
        $stmt->execute([$escola_id]);
        return $stmt->fetchAll();
    }
}
