<?php
// models/Receita.php

require_once __DIR__ . '/../config/database.php';

class Receita {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function list(int $escola_id, array $filters = [], bool $lixeira = false): array {
        $sql = "SELECT r.*, a.nome as aluno_nome
                FROM receitas r
                LEFT JOIN alunos a ON a.id = r.aluno_id
                WHERE r.escola_id = :escola_id AND r.eliminado = :eliminado";
        $params = [':escola_id' => $escola_id, ':eliminado' => $lixeira ? 1 : 0];

        if (!empty($filters['mes'])) {
            $sql .= " AND DATE_FORMAT(r.data, '%Y-%m') = :mes";
            $params[':mes'] = $filters['mes'];
        }
        if (!empty($filters['tipo'])) {
            $sql .= " AND r.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }
        if (!empty($filters['aluno_id'])) {
            $sql .= " AND r.aluno_id = :aluno_id";
            $params[':aluno_id'] = $filters['aluno_id'];
        }
        if (!empty($filters['metodo'])) {
            $sql .= " AND r.metodo = :metodo";
            $params[':metodo'] = $filters['metodo'];
        }
        if (!empty($filters['valor_min'])) {
            $sql .= " AND r.valor >= :valor_min";
            $params[':valor_min'] = $filters['valor_min'];
        }
        if (!empty($filters['valor_max'])) {
            $sql .= " AND r.valor <= :valor_max";
            $params[':valor_max'] = $filters['valor_max'];
        }
        $sql .= " ORDER BY r.data DESC, r.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO receitas (escola_id, aluno_id, tipo, valor, data, metodo, descricao)
             VALUES (:escola_id, :aluno_id, :tipo, :valor, :data, :metodo, :descricao)"
        );
        $stmt->execute([
            ':escola_id' => $data['escola_id'],
            ':aluno_id'  => $data['aluno_id'] ?: null,
            ':tipo'      => $data['tipo'],
            ':valor'     => $data['valor'],
            ':data'      => $data['data'],
            ':metodo'    => $data['metodo'],
            ':descricao' => $data['descricao'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function softDelete(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "UPDATE receitas SET eliminado=1, eliminado_em=NOW()
             WHERE id = ? AND escola_id = ? AND eliminado = 0"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function restore(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "UPDATE receitas SET eliminado=0, eliminado_em=NULL
             WHERE id = ? AND escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function hardDelete(int $id, int $escola_id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM receitas WHERE id = ? AND escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->rowCount() > 0;
    }

    public function totalByMonth(int $escola_id, string $mes): float {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM receitas
             WHERE escola_id = ? AND DATE_FORMAT(data, '%Y-%m') = ? AND eliminado = 0"
        );
        $stmt->execute([$escola_id, $mes]);
        return (float)$stmt->fetchColumn();
    }

    public function findById(int $id, int $escola_id): ?array {
        $stmt = $this->db->prepare(
            "SELECT r.*, a.nome as aluno_nome FROM receitas r
             LEFT JOIN alunos a ON a.id = r.aluno_id
             WHERE r.id = ? AND r.escola_id = ?"
        );
        $stmt->execute([$id, $escola_id]);
        return $stmt->fetch() ?: null;
    }

    public function countLixeira(int $escola_id): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM receitas WHERE escola_id = ? AND eliminado = 1"
        );
        $stmt->execute([$escola_id]);
        return (int)$stmt->fetchColumn();
    }

    public function totalByTipo(int $escola_id, string $mes): array {
        $stmt = $this->db->prepare(
            "SELECT tipo, COUNT(*) as qtd, SUM(valor) as total
             FROM receitas WHERE escola_id = ? AND DATE_FORMAT(data,'%Y-%m') = ? AND eliminado = 0
             GROUP BY tipo ORDER BY total DESC"
        );
        $stmt->execute([$escola_id, $mes]);
        return $stmt->fetchAll();
    }
}
