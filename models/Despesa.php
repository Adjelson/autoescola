<?php
// models/Despesa.php

require_once __DIR__ . '/../config/database.php';

class Despesa {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function list(int $escola_id, array $filters = [], bool $lixeira = false): array {
        $sql = "SELECT * FROM despesas WHERE escola_id = :escola_id AND eliminado = :eliminado";
        $params = [':escola_id' => $escola_id, ':eliminado' => $lixeira ? 1 : 0];

        if (!empty($filters['mes'])) {
            $sql .= " AND DATE_FORMAT(data, '%Y-%m') = :mes";
            $params[':mes'] = $filters['mes'];
        }
        if (!empty($filters['categoria'])) {
            $sql .= " AND categoria = :categoria";
            $params[':categoria'] = $filters['categoria'];
        }
        if (!empty($filters['valor_min'])) {
            $sql .= " AND valor >= :valor_min";
            $params[':valor_min'] = $filters['valor_min'];
        }
        if (!empty($filters['valor_max'])) {
            $sql .= " AND valor <= :valor_max";
            $params[':valor_max'] = $filters['valor_max'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND descricao LIKE :q";
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        $sql .= " ORDER BY data DESC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO despesas (escola_id, categoria, valor, data, descricao)
             VALUES (:escola_id, :categoria, :valor, :data, :descricao)"
        );
        $stmt->execute([
            ':escola_id'  => $data['escola_id'],
            ':categoria'  => $data['categoria'],
            ':valor'      => $data['valor'],
            ':data'       => $data['data'],
            ':descricao'  => $data['descricao'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id, int $escola_id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM despesas WHERE id = ? AND escola_id = ?"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $id, int $escola_id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE despesas SET categoria=:categoria, valor=:valor, data=:data, descricao=:descricao
             WHERE id=:id AND escola_id=:escola_id AND eliminado=0"
        );
        $stmt->execute([
            ':categoria' => $data['categoria'],
            ':valor'     => $data['valor'],
            ':data'      => $data['data'],
            ':descricao' => $data['descricao'],
            ':id'        => $id,
            ':escola_id' => $escola_id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "UPDATE despesas SET eliminado=1, eliminado_em=NOW()
             WHERE id = ? AND escola_id = ? AND eliminado = 0"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "UPDATE despesas SET eliminado=0, eliminado_em=NULL
             WHERE id = ? AND escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM despesas WHERE id = ? AND escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function totalByMonth(int $escola_id, string $mes): float {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM despesas
             WHERE escola_id = ? AND DATE_FORMAT(data, '%Y-%m') = ? AND eliminado = 0"
        );
        $stmt->execute([$escola_id, $mes]);
        return (float)$stmt->fetchColumn();
    }

    public function totalByCategory(int $escola_id, string $mes): array {
        $stmt = $this->db->prepare(
            "SELECT categoria, SUM(valor) as total
             FROM despesas WHERE escola_id = ? AND DATE_FORMAT(data, '%Y-%m') = ? AND eliminado = 0
             GROUP BY categoria ORDER BY total DESC"
        );
        $stmt->execute([$escola_id, $mes]);
        return $stmt->fetchAll();
    }

    public function countLixeira(int $escola_id): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM despesas WHERE escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$escola_id]);
        return (int)$stmt->fetchColumn();
    }
}
