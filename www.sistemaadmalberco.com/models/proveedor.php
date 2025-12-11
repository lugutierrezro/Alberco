<?php
/**
 * Modelo Proveedor
 */

require_once 'database.php';

class Proveedor extends BaseModel {
    protected $table = 'tb_proveedores';
    protected $primaryKey = 'id_proveedor';

    /**
     * Obtener con estadísticas de compras
     */
    public function getAllWithStats() {
        return $this->query(
            "SELECT p.*, 
                COUNT(c.id_compra) as total_compras,
                SUM(c.total) as total_gastado,
                MAX(c.fecha_compra) as ultima_compra
            FROM {$this->table} p
            LEFT JOIN tb_compras c ON p.id_proveedor = c.id_proveedor 
                AND c.estado_registro = 'ACTIVO'
            WHERE p.estado_registro = 'ACTIVO'
            GROUP BY p.id_proveedor
            ORDER BY p.nombre_proveedor"
        );
    }

    /**
     * Buscar por código
     */
    public function getByCodigo($codigo) {
        if (empty($codigo)) return false;

        $result = $this->findBy('codigo_proveedor', $codigo);
        return !empty($result) ? $result[0] : false;
    }

    /**
     * Buscar por RUC
     */
    public function getByRuc($ruc) {
        if (empty($ruc)) return false;

        $result = $this->findBy('ruc', $ruc);
        return !empty($result) ? $result[0] : false;
    }

    /**
     * Verificar si código existe
     */
    public function codigoExists($codigo, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE codigo_proveedor = :codigo";
            
            if (!empty($excludeId)) {
                $sql .= " AND id_proveedor != :exclude_id";
            }

            $stmt = $this->pdo->prepare($sql);
            $params = [':codigo' => $codigo];
            
            if (!empty($excludeId)) {
                $params[':exclude_id'] = $excludeId;
            }

            $stmt->execute($params);
            $result = $stmt->fetch();

            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('codigoExists', $e);
            return false;
        }
    }

    /**
     * Buscar proveedores
     */
    public function search($search) {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE (nombre_proveedor LIKE :search 
                OR empresa LIKE :search
                OR ruc LIKE :search
                OR codigo_proveedor LIKE :search)
            AND estado_registro = 'ACTIVO'
            ORDER BY nombre_proveedor",
            [':search' => "%$search%"]
        );
    }

    /**
     * Obtener historial de compras
     */
    public function getHistorialCompras($proveedorId) {
        if (empty($proveedorId)) {
            return false;
        }

        return $this->query(
            "SELECT c.*, p.nombre as producto_nombre,
                u.username as usuario
            FROM tb_compras c
            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
            INNERJOIN tb_usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_proveedor = :proveedor_id
            AND c.estado_registro = 'ACTIVO'
            ORDER BY c.fecha_compra DESC",
            [':proveedor_id' => $proveedorId]
        );
    }
}
