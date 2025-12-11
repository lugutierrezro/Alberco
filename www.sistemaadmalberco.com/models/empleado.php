<?php
/**
 * Modelo Empleado
 */

require_once 'database.php';

class Empleado extends BaseModel {
    protected $table = 'tb_empleados';
    protected $primaryKey = 'id_empleado';
    
    /**
     * Obtener empleados con su rol
     */
    public function getAllWithRole() {
        return $this->query(
            "SELECT e.*, r.rol 
            FROM {$this->table} e
            INNER JOIN tb_roles r ON e.id_rol = r.id_rol
            WHERE e.estado_registro = 'ACTIVO'
            ORDER BY e.nombres, e.apellidos"
        );
    }
    
    /**
     * Obtener empleados por rol
     */
    public function getByRole($rolId) {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE id_rol = :rol_id 
            AND estado_registro = 'ACTIVO'
            ORDER BY nombres, apellidos",
            [':rol_id' => $rolId]
        );
    }
    
    /**
     * Obtener empleados activos laboralmente
     */
    public function getActivos() {
        return $this->query(
            "SELECT e.*, r.rol 
            FROM {$this->table} e
            INNER JOIN tb_roles r ON e.id_rol = r.id_rol
            WHERE e.estado_laboral = 'ACTIVO'
            AND e.estado_registro = 'ACTIVO'
            ORDER BY e.nombres"
        );
    }
    
    /**
     * Obtener empleados de delivery disponibles
     */
    public function getDeliverysDisponibles() {
        return $this->query(
            "SELECT e.* 
            FROM {$this->table} e
            WHERE e.id_rol = (SELECT id_rol FROM tb_roles WHERE rol = 'DELIVERY')
            AND e.estado_laboral = 'ACTIVO'
            AND e.estado_registro = 'ACTIVO'
            ORDER BY e.nombres"
        );
    }
    
    /**
     * Buscar por código
     */
    public function getByCodigo($codigo) {
        $result = $this->findBy('codigo_empleado', $codigo);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Buscar por documento
     */
    public function getByDocumento($numeroDocumento) {
        $result = $this->findBy('numero_documento', $numeroDocumento);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Verificar si código existe
     */
    public function codigoExists($codigo, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE codigo_empleado = :codigo";
            
            if ($excludeId) {
                $sql .= " AND id_empleado != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':codigo' => $codigo];
            
            if ($excludeId) {
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
     * Verificar si documento existe
     */
    public function documentoExists($numeroDocumento, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE numero_documento = :documento";
            
            if ($excludeId) {
                $sql .= " AND id_empleado != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':documento' => $numeroDocumento];
            
            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('documentoExists', $e);
            return false;
        }
    }
    
    /**
     * Actualizar estado laboral
     */
    public function updateEstadoLaboral($empleadoId, $estado) {
        return $this->update($empleadoId, [
            'estado_laboral' => $estado
        ]);
    }
    
    /**
     * Buscar empleados
     */
    public function search($search) {
        return $this->query(
            "SELECT e.*, r.rol 
            FROM {$this->table} e
            INNER JOIN tb_roles r ON e.id_rol = r.id_rol
            WHERE (e.nombres LIKE :search 
                OR e.apellidos LIKE :search
                OR e.codigo_empleado LIKE :search
                OR e.numero_documento LIKE :search
                OR e.email LIKE :search)
            AND e.estado_registro = 'ACTIVO'
            ORDER BY e.nombres, e.apellidos",
            [':search' => "%$search%"]
        );
    }
    
    /**
     * Obtener estadísticas de un empleado delivery
     */
    public function getEstadisticasDelivery($empleadoId, $fechaInicio = null, $fechaFin = null) {
        try {
            $fechaInicio = $fechaInicio ?? date('Y-m-01');
            $fechaFin = $fechaFin ?? date('Y-m-d');
            
            $sql = "CALL sp_estadisticas_delivery(:id_empleado, :fecha_inicio, :fecha_fin)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_empleado' => $empleadoId,
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getEstadisticasDelivery', $e);
            return false;
        }
    }
    
    /**
     * Obtener empleados con su cuenta de usuario vinculada (por email)
     */
    public function getConUsuario() {
        return $this->query(
            "SELECT e.*, r.rol,
                    u.id_usuario, u.username, u.email as email_usuario,
                    u.estado_registro as usuario_estado
             FROM {$this->table} e
             INNER JOIN tb_roles r ON e.id_rol = r.id_rol
             LEFT JOIN tb_usuarios u ON e.email = u.email
             WHERE e.estado_registro = 'ACTIVO'
             ORDER BY e.nombres, e.apellidos"
        );
    }
    
    /**
     * Obtener empleados sin cuenta de usuario
     */
    public function getSinCuenta() {
        return $this->query(
            "SELECT e.*, r.rol
             FROM {$this->table} e
             INNER JOIN tb_roles r ON e.id_rol = r.id_rol
             LEFT JOIN tb_usuarios u ON e.email = u.email
             WHERE e.estado_registro = 'ACTIVO'
             AND u.id_usuario IS NULL
             ORDER BY e.nombres, e.apellidos"
        );
    }
    
    /**
     * Crear cuenta de usuario para un empleado
     */
    public function crearCuenta($empleadoId) {
        try {
            // Obtener datos del empleado
            $empleado = $this->getById($empleadoId);
            if (!$empleado) {
                return ['success' => false, 'message' => 'Empleado no encontrado'];
            }
            
            // Verificar que no tenga cuenta ya
            $checkUser = $this->query(
                "SELECT * FROM tb_usuarios WHERE email = :email",
                [':email' => $empleado['email']]
            );
            
            if (!empty($checkUser)) {
                return ['success' => false, 'message' => 'El empleado ya tiene cuenta de usuario'];
            }
            
            // Generar username
            $username = 'emp_' . strtolower($empleado['codigo_empleado']);
            
            // Verificar que username no exista
            $checkUsername = $this->query(
                "SELECT * FROM tb_usuarios WHERE username = :username",
                [':username' => $username]
            );
            
            if (!empty($checkUsername)) {
                $username .= '_' . rand(100, 999);
            }
            
            // Contraseña = documento
            $password = password_hash($empleado['numero_documento'], PASSWORD_DEFAULT);
            
            // Crear usuario
            $sql = "INSERT INTO tb_usuarios 
                    (username, email, password_user, nombres_usuario, id_rol, 
                     estado_registro, fyh_creacion, fyh_actualizacion)
                    VALUES 
                    (:username, :email, :password, :nombres, :id_rol,
                     'ACTIVO', NOW(), NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $empleado['email'],
                ':password' => $password,
                ':nombres' => $empleado['nombres'] . ' ' . $empleado['apellidos'],
                ':id_rol' => $empleado['id_rol']
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'username' => $username,
                    'password_texto' => $empleado['numero_documento'],
                    'message' => 'Cuenta creada exitosamente'
                ];
            }
            
            return ['success' => false, 'message' => 'Error al crear la cuenta'];
            
        } catch(PDOException $e) {
            $this->logError('crearCuenta', $e);
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

