<?php
/**
 * Modelo Anuncio
 * Gestiona los anuncios y banners del sitio web
 */

require_once __DIR__ . '/database.php';

class Anuncio extends BaseModel {
    protected $table = 'tb_anuncios_sitio';
    protected $primaryKey = 'id_anuncio';
    
    /**
     * Obtener todos los anuncios (sobrescrito para usar columna correcta)
     */
    public function getAll($includeInactive = false) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!$includeInactive) {
                $sql .= " WHERE estado_registro = 'ACTIVO'";
            }
            
            $sql .= " ORDER BY prioridad DESC, fecha_inicio DESC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getAll', $e);
            return [];
        }
    }
    
    /**
     * Obtener anuncios activos
     */
    public function getActivos() {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND estado_registro = 'ACTIVO'
                    AND (fecha_inicio IS NULL OR fecha_inicio <= NOW())
                    AND (fecha_fin IS NULL OR fecha_fin >= NOW())
                    ORDER BY prioridad DESC, fecha_creacion DESC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getActivos', $e);
            return [];
        }
    }
    
    /**
     * Obtener anuncios por posición
     */
    public function getByPosicion($posicion) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND posicion = :posicion
                    AND estado_registro = 'ACTIVO'
                    AND (fecha_inicio IS NULL OR fecha_inicio <= NOW())
                    AND (fecha_fin IS NULL OR fecha_fin >= NOW())
                    ORDER BY prioridad DESC";
            
            return $this->query($sql, [':posicion' => $posicion]);
        } catch(PDOException $e) {
            $this->logError('getByPosicion', $e);
            return [];
        }
    }
    
    /**
     * Crear nuevo anuncio
     */
    public function crearAnuncio($datos) {
        try {
            $this->beginTransaction();
            
            $resultado = $this->create([
                'titulo' => $datos['titulo'],
                'contenido' => $datos['contenido'] ?? '',
                'tipo' => $datos['tipo'] ?? 'info',
                'prioridad' => $datos['prioridad'] ?? 1,
                'fecha_inicio' => $datos['fecha_inicio'] ?? null,
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'activo' => $datos['activo'] ?? 1,
                'posicion' => $datos['posicion'] ?? 'top',
                'estilo_css' => $datos['estilo_css'] ?? null,
                'id_usuario_creador' => $datos['id_usuario'] ?? null,
                'estado_registro' => 'ACTIVO'
            ]);
            
            $this->commit();
            return $resultado;
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('crearAnuncio', $e);
            return false;
        }
    }
    
    /**
     * Actualizar anuncio
     */
    public function actualizarAnuncio($id, $datos) {
        try {
            return $this->update($id, $datos);
        } catch(Exception $e) {
            $this->logError('actualizarAnuncio', $e);
            return false;
        }
    }
    
    /**
     * Activar/Desactivar anuncio
     */
    public function toggleActivo($id) {
        try {
            $anuncio = $this->getById($id);
            if (!$anuncio) return false;
            
            $nuevoEstado = $anuncio['activo'] ? 0 : 1;
            return $this->update($id, ['activo' => $nuevoEstado]);
            
        } catch(Exception $e) {
            $this->logError('toggleActivo', $e);
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de anuncios
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN DATE(fecha_fin) < CURDATE() THEN 1 ELSE 0 END) as expirados
                    FROM {$this->table}
                    WHERE estado_registro = 'ACTIVO'";
            
            $result = $this->query($sql);
            return $result[0] ?? [];
        } catch(PDOException $e) {
            $this->logError('getEstadisticas', $e);
            return [];
        }
    }
}
