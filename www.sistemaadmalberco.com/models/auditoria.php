<?php
/**
 * Modelo para gestionar el historial de actividades (auditoría)
 */

require_once __DIR__ . '/database.php';

class Auditoria extends BaseModel {
    protected $table = 'tb_auditoria';
    protected $primaryKey = 'id_auditoria';
    protected $softDelete = false; // Los logs de auditoría no se eliminan
    
    /**
     * Obtener todas las actividades con filtros
     */
    public function getActividades($filtros = []) {
        try {
            $sql = "SELECT 
                        a.*,
                        u.username,
                        CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo
                    FROM tb_auditoria a
                    LEFT JOIN tb_usuarios u ON a.id_usuario = u.id_usuario
                    LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                    WHERE 1=1";
            
            $params = [];
            
            // Filtro por usuario
            if (!empty($filtros['id_usuario'])) {
                $sql .= " AND a.id_usuario = :id_usuario";
                $params[':id_usuario'] = $filtros['id_usuario'];
            }
            
            // Filtro por módulo
            if (!empty($filtros['modulo'])) {
                $sql .= " AND a.modulo = :modulo";
                $params[':modulo'] = $filtros['modulo'];
            }
            
            // Filtro por acción
            if (!empty($filtros['accion'])) {
                $sql .= " AND a.accion = :accion";
                $params[':accion'] = $filtros['accion'];
            }
            
            // Filtro por nivel
            if (!empty($filtros['nivel'])) {
                $sql .= " AND a.nivel = :nivel";
                $params[':nivel'] = $filtros['nivel'];
            }
            
            // Filtro por rango de fechas
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(a.fecha_accion) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(a.fecha_accion) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            // Búsqueda en descripción
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.descripcion LIKE :busqueda OR a.modulo LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            $sql .= " ORDER BY a.fecha_accion DESC";
            
            // Paginación
            if (!empty($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params[':limite'] = (int)$filtros['limite'];
                
                if (!empty($filtros['offset'])) {
                    $sql .= " OFFSET :offset";
                    $params[':offset'] = (int)$filtros['offset'];
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            
            // Bind de parámetros
            foreach ($params as $key => $value) {
                if ($key === ':limite' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getActividades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar actividades con filtros
     */
    public function contarActividades($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM tb_auditoria a WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['id_usuario'])) {
                $sql .= " AND a.id_usuario = :id_usuario";
                $params[':id_usuario'] = $filtros['id_usuario'];
            }
            
            if (!empty($filtros['modulo'])) {
                $sql .= " AND a.modulo = :modulo";
                $params[':modulo'] = $filtros['modulo'];
            }
            
            if (!empty($filtros['accion'])) {
                $sql .= " AND a.accion = :accion";
                $params[':accion'] = $filtros['accion'];
            }
            
            if (!empty($filtros['nivel'])) {
                $sql .= " AND a.nivel = :nivel";
                $params[':nivel'] = $filtros['nivel'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(a.fecha_accion) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(a.fecha_accion) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (a.descripcion LIKE :busqueda OR a.modulo LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Error en contarActividades: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas del historial
     */
    public function getEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        try {
            $params = [];
            $whereClause = "WHERE 1=1";
            
            if ($fecha_desde) {
                $whereClause .= " AND DATE(fecha_accion) >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $whereClause .= " AND DATE(fecha_accion) <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }
            
            // Total de actividades
            $sql = "SELECT COUNT(*) as total FROM tb_auditoria $whereClause";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Por módulo
            $sql = "SELECT modulo, COUNT(*) as total 
                    FROM tb_auditoria 
                    $whereClause
                    GROUP BY modulo 
                    ORDER BY total DESC 
                    LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $por_modulo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Por usuario (top 10)
            $sql = "SELECT 
                        a.id_usuario,
                        u.username,
                        CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                        COUNT(*) as total
                    FROM tb_auditoria a
                    LEFT JOIN tb_usuarios u ON a.id_usuario = u.id_usuario
                    LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                    $whereClause
                    GROUP BY a.id_usuario, u.username, nombre_completo
                    ORDER BY total DESC 
                    LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Por nivel
            $sql = "SELECT nivel, COUNT(*) as total 
                    FROM tb_auditoria 
                    $whereClause
                    GROUP BY nivel";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $por_nivel = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Por acción
            $sql = "SELECT accion, COUNT(*) as total 
                    FROM tb_auditoria 
                    $whereClause
                    GROUP BY accion 
                    ORDER BY total DESC 
                    LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $por_accion = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'por_modulo' => $por_modulo,
                'por_usuario' => $por_usuario,
                'por_nivel' => $por_nivel,
                'por_accion' => $por_accion
            ];
            
        } catch (PDOException $e) {
            error_log("Error en getEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'por_modulo' => [],
                'por_usuario' => [],
                'por_nivel' => [],
                'por_accion' => []
            ];
        }
    }
    
    /**
     * Obtener módulos únicos
     */
    public function getModulos() {
        try {
            $sql = "SELECT DISTINCT modulo FROM tb_auditoria WHERE modulo IS NOT NULL ORDER BY modulo";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtener acciones únicas
     */
    public function getAcciones() {
        try {
            $sql = "SELECT DISTINCT accion FROM tb_auditoria WHERE accion IS NOT NULL ORDER BY accion";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Limpiar registros antiguos
     */
    public function limpiarAntiguos($dias = 90) {
        try {
            $sql = "DELETE FROM tb_auditoria 
                    WHERE fecha_accion < DATE_SUB(NOW(), INTERVAL :dias DAY)
                    AND nivel = 'info'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':dias' => $dias]);
            
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            error_log("Error en limpiarAntiguos: " . $e->getMessage());
            return 0;
        }
    }
}
