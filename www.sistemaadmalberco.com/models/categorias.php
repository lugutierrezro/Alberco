<?php
/**
 * Modelo Categoria
 */

require_once 'database.php';

class Categoria extends BaseModel {
    protected $table = 'tb_categorias';
    protected $primaryKey = 'id_categoria';
    
    /**
     * Obtener todas ordenadas
     */
    public function getAllOrdered() {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE estado_registro = 'ACTIVO'
            ORDER BY orden ASC, nombre_categoria ASC"
        );
    }
    
    /**
     * Obtener con conteo de productos
     */
    public function getAllWithProductCount() {
        return $this->query(
            "SELECT c.*, 
                COUNT(a.id_producto) as total_productos,
                SUM(CASE WHEN a.disponible_venta = 1 AND a.stock > 0 THEN 1 ELSE 0 END) as productos_disponibles
            FROM {$this->table} c
            LEFT JOIN tb_almacen a ON c.id_categoria = a.id_categoria 
                AND a.estado_registro = 'ACTIVO'
            WHERE c.estado_registro = 'ACTIVO'
            GROUP BY c.id_categoria
            ORDER BY c.orden ASC"
        );
    }
    
    /**
     * Verificar si nombre existe
     */
    public function nombreExists($nombre, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE nombre_categoria = :nombre";
            
            if ($excludeId) {
                $sql .= " AND id_categoria != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':nombre' => $nombre];
            
            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('nombreExists', $e);
            return false;
        }
    }
    
    /**
     * Actualizar orden
     */
    public function updateOrden($categoriaId, $orden) {
        return $this->update($categoriaId, ['orden' => $orden]);
    }
    
    /**
     * Reordenar categorías
     */
    public function reordenar($ordenArray) {
        try {
            $this->beginTransaction();
            
            foreach ($ordenArray as $orden => $categoriaId) {
                $this->updateOrden($categoriaId, $orden + 1);
            }
            
            $this->commit();
            return true;
        } catch(PDOException $e) {
            $this->rollback();
            $this->logError('reordenar', $e);
            return false;
        }
    }
}
