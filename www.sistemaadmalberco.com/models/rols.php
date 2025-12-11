<?php
/**
 * Modelo Rol
 */

require_once 'database.php';

class Rol extends BaseModel {
    protected $table = 'tb_roles';
    protected $primaryKey = 'id_rol';
    
    /**
     * Obtener rol por nombre
     */
    public function getByNombre($nombreRol) {
        $result = $this->findBy('rol', $nombreRol);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Obtener con cantidad de usuarios
     */
    public function getAllWithUserCount() {
        return $this->query(
            "SELECT r.*, 
                COUNT(u.id_usuario) as total_usuarios,
                COUNT(CASE WHEN u.estado_registro = 'ACTIVO' THEN 1 END) as usuarios_activos
            FROM {$this->table} r
            LEFT JOIN tb_usuarios u ON r.id_rol = u.id_rol
            WHERE r.estado_registro = 'ACTIVO'
            GROUP BY r.id_rol
            ORDER BY r.rol"
        );
    }
    
    /**
     * Actualizar permisos
     */
    public function updatePermisos($rolId, $permisos) {
        return $this->update($rolId, [
            'permisos' => json_encode($permisos)
        ]);
    }
    
    /**
     * Obtener permisos
     */
    public function getPermisos($rolId) {
        $rol = $this->getById($rolId);
        
        if ($rol && isset($rol['permisos'])) {
            return json_decode($rol['permisos'], true);
        }
        
        return [];
    }
}
