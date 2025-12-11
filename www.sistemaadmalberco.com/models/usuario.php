<?php
/**
 * Modelo Usuario - Compatible con config.php
 */

require_once 'database.php';

class Usuario extends BaseModel {
    protected $table = 'tb_usuarios';
    protected $primaryKey = 'id_usuario';
    
    /**
     * Autenticar usuario
     */
    public function authenticate($email, $password) {
        try {
            $sql = "SELECT u.*, r.rol, e.nombres, e.apellidos 
                    FROM {$this->table} u
                    INNER JOIN tb_roles r ON u.id_rol = r.id_rol
                    LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                    WHERE u.email = :email 
                    AND u.estado_registro = 'ACTIVO'
                    AND u.bloqueado = 0";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_user'])) {
                $this->updateLastAccess($user['id_usuario']);
                $this->resetFailedAttempts($user['id_usuario']);
                
                // Guardar en sesión
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombres'] . ' ' . $user['apellidos'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                $_SESSION['user_role_id'] = $user['id_rol'];
                $_SESSION['sesion'] = 'ok';
                
                return $user;
            }
            
            if ($user) {
                $this->incrementFailedAttempts($user['id_usuario']);
            }
            
            return false;
        } catch(PDOException $e) {
            $this->logError('authenticate', $e);
            return false;
        }
    }
    
    /**
     * Crear usuario
     */
    public function createUser($data) {
        if (isset($data['password_user'])) {
            $data['password_user'] = password_hash($data['password_user'], PASSWORD_BCRYPT);
        }
        
        if (!isset($data['token'])) {
            $data['token'] = generarToken();
        }
        
        return $this->create($data);
    }
    
    /**
     * Actualizar contraseña
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        return $this->update($userId, [
            'password_user' => $hashedPassword
        ]);
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE email = :email";
            
            if ($excludeId) {
                $sql .= " AND id_usuario != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':email' => $email];
            
            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('emailExists', $e);
            return false;
        }
    }
    
    /**
     * Verificar si usuario está autenticado
     */
    public static function isAuthenticated() {
        return isset($_SESSION['sesion']) && $_SESSION['sesion'] === 'ok';
    }
    
    /**
     * Obtener usuario actual de la sesión
     */
    public static function getCurrentUser() {
        if (self::isAuthenticated()) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? null,
                'email' => $_SESSION['user_email'] ?? null,
                'role' => $_SESSION['user_role'] ?? null,
                'role_id' => $_SESSION['user_role_id'] ?? null
            ];
        }
        return null;
    }
    
    /**
     * Cerrar sesión
     */
    public static function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Obtener por rol
     */
    public function getByRole($rolId) {
        return $this->query(
            "SELECT u.*, e.nombres, e.apellidos, r.rol
            FROM {$this->table} u
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            WHERE u.id_rol = :rol_id 
            AND u.estado_registro = 'ACTIVO'
            ORDER BY e.nombres",
            [':rol_id' => $rolId]
        );
    }
    
    // Métodos privados
    private function updateLastAccess($userId) {
        $this->execute(
            "UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE id_usuario = :user_id",
            [':user_id' => $userId]
        );
    }
    
    private function incrementFailedAttempts($userId) {
        $this->execute(
            "UPDATE {$this->table} 
            SET intentos_fallidos = intentos_fallidos + 1,
                bloqueado = CASE WHEN intentos_fallidos >= 4 THEN 1 ELSE 0 END
            WHERE id_usuario = :user_id",
            [':user_id' => $userId]
        );
    }
    
    private function resetFailedAttempts($userId) {
        $this->execute(
            "UPDATE {$this->table} SET intentos_fallidos = 0 WHERE id_usuario = :user_id",
            [':user_id' => $userId]
        );
    }
}
