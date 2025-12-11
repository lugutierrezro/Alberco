<?php
/**
 * Clase Base Model - Adaptada para usar PDO global
 * Sistema de Gestión Alberco
 */

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';
    protected $softDelete = true;
    protected $softDeleteField = 'estado_registro';
    
    public function __construct() {
        // Usar la conexión PDO global
        $this->pdo = getDB();
    }
    
    /**
     * Obtener todos los registros
     * @param bool $includeInactive
     * @return array
     */
    public function getAll($includeInactive = false) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if ($this->softDelete && !$includeInactive) {
                $sql .= " WHERE {$this->softDeleteField} = 'ACTIVO'";
            }
            
            $sql .= " ORDER BY fyh_creacion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getAll', $e);
            return [];
        }
    }
    
    /**
     * Obtener por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
            
            if ($this->softDelete) {
                $sql .= " AND {$this->softDeleteField} = 'ACTIVO'";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getById', $e);
            return false;
        }
    }
    
    /**
     * Crear registro
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        try {
            $fields = array_keys($data);
            $placeholders = array_map(function($field) {
                return ":$field";
            }, $fields);
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            if ($stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
        } catch(PDOException $e) {
            $this->logError('create', $e);
            return false;
        }
    }
    
    /**
     * Actualizar registro
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $fields = array_map(function($field) {
                return "$field = :$field";
            }, array_keys($data));
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
                   " WHERE {$this->primaryKey} = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->logError('update', $e);
            return false;
        }
    }
    
    /**
     * Eliminar (soft delete)
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete($id, $userId = null) {
        try {
            if ($this->softDelete) {
                $sql = "UPDATE {$this->table} 
                        SET {$this->softDeleteField} = 'INACTIVO' 
                        WHERE {$this->primaryKey} = :id";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $result = $stmt->execute();
                
                if ($result && $userId) {
                    $this->logAudit($id, 'SOFT_DELETE', $userId);
                }
                
                return $result;
            } else {
                $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                return $stmt->execute();
            }
        } catch(PDOException $e) {
            $this->logError('delete', $e);
            return false;
        }
    }
    
    /**
     * Restaurar registro
     * @param int $id
     * @return bool
     */
    public function restore($id) {
        try {
            if (!$this->softDelete) {
                return false;
            }
            
            $sql = "UPDATE {$this->table} 
                    SET {$this->softDeleteField} = 'ACTIVO' 
                    WHERE {$this->primaryKey} = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            $this->logError('restore', $e);
            return false;
        }
    }
    
    /**
     * Buscar por campo
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findBy($field, $value) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE $field = :value";
            
            if ($this->softDelete) {
                $sql .= " AND {$this->softDeleteField} = 'ACTIVO'";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('findBy', $e);
            return [];
        }
    }
    
    /**
     * Contar registros
     * @param string $condition
     * @return int
     */
    public function count($condition = '') {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            
            if ($this->softDelete) {
                $sql .= " WHERE {$this->softDeleteField} = 'ACTIVO'";
                if (!empty($condition)) {
                    $sql .= " AND $condition";
                }
            } elseif (!empty($condition)) {
                $sql .= " WHERE $condition";
            }
            
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            
            return (int)$result['total'];
        } catch(PDOException $e) {
            $this->logError('count', $e);
            return 0;
        }
    }
    
    /**
     * Ejecutar query personalizado
     * @param string $sql
     * @param array $params
     * @return array
     */
    public  function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('query', $e);
            return [];
        }
    }
    
    /**
     * Ejecutar query sin retorno de datos
     * @param string $sql
     * @param array $params
     * @return bool
     */
    protected function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            $this->logError('execute', $e);
            return false;
        }
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        $this->pdo->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        $this->pdo->rollBack();
    }
    
    /**
     * Registrar en auditoría
     * @param int $recordId
     * @param string $action
     * @param int $userId
     */
    protected function logAudit($recordId, $action, $userId) {
        try {
            $sql = "INSERT INTO tb_auditoria (tabla_afectada, id_registro_afectado, accion, id_usuario) 
                    VALUES (:tabla, :record_id, :action, :user_id)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tabla' => $this->table,
                ':record_id' => $recordId,
                ':action' => $action,
                ':user_id' => $userId
            ]);
        } catch(PDOException $e) {
            error_log("Error en logAudit: " . $e->getMessage());
        }
    }
    
    /**
     * Log de errores
     * @param string $method
     * @param PDOException $e
     */
    protected function logError($method, $e) {
        $message = sprintf(
            "[%s] Error en %s::%s - %s (Archivo: %s, Línea: %d)",
            getFechaHora(),
            get_class($this),
            $method,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        
        error_log($message);
        
        if (ENVIRONMENT === 'development') {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "<strong>Error en {$method}:</strong><br>";
            echo $e->getMessage() . "<br>";
            echo "<small>Archivo: {$e->getFile()} | Línea: {$e->getLine()}</small>";
            echo "</div>";
        }
    }
}
