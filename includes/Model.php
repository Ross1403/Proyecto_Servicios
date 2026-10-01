<?php
// ============================================================
// includes/Model.php — Base Model (Architecture Pro)
// Abstracción ORM ligera para interactuar con la Base de Datos
// ============================================================

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Obtiene todos los registros, opcionalmente con condiciones.
     */
    public function all($conditions = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $clauses = [];
            foreach ($conditions as $key => $value) {
                $clauses[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= implode(" AND ", $clauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca un registro por ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Inserta un nuevo registro.
     */
    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualiza un registro por ID.
     */
    public function update($id, $data) {
        $set = "";
        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key}, ";
        }
        $set = rtrim($set, ", ");
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Elimina un registro por ID.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute([':id' => $id]);
    }
}
