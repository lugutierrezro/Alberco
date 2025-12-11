<?php
/**
 * Modelo Evento
 * Gestiona eventos con temporizadores de cuenta regresiva
 */

require_once __DIR__ . '/database.php';

class Evento extends BaseModel {
    protected $table = 'tb_eventos_temporizador';
    protected $primaryKey = 'id_evento';
    
    /**
     * Obtener todos los eventos (sobrescrito para usar columna correcta)
     */
    public function getAll($includeInactive = false) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!$includeInactive) {
                $sql .= " WHERE estado_registro = 'ACTIVO'";
            }
            
            $sql .= " ORDER BY fecha_evento DESC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getAll', $e);
            return [];
        }
    }
    
    /**
     * Obtener eventos activos
     */
    public function getActivos() {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND estado_registro = 'ACTIVO'
                    AND fecha_evento >= NOW()
                    ORDER BY fecha_evento ASC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getActivos', $e);
            return [];
        }
    }
    
    /**
     * Obtener próximo evento
     */
    public function getProximo() {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND estado_registro = 'ACTIVO'
                    AND fecha_evento >= NOW()
                    ORDER BY fecha_evento ASC
                    LIMIT 1";
            
            $result = $this->query($sql);
            return !empty($result) ? $result[0] : null;
        } catch(PDOException $e) {
            $this->logError('getProximo', $e);
            return null;
        }
    }
    
    /**
     * Obtener evento actual (en curso)
     */
    public function getEnCurso() {
        try {
            // Un evento está "en curso" si es hoy o dentro de las próximas 24 horas
            $sql = "SELECT * FROM {$this->table} 
                    WHERE activo = 1 
                    AND mostrar_contador = 1
                    AND estado_registro = 'ACTIVO'
                    AND fecha_evento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                    ORDER BY fecha_evento ASC
                    LIMIT 1";
            
            $result = $this->query($sql);
            return !empty($result) ? $result[0] : null;
        } catch(PDOException $e) {
            $this->logError('getEnCurso', $e);
            return null;
        }
    }
    
    /**
     * Crear nuevo evento
     */
    public function crearEvento($datos) {
        try {
            $this->beginTransaction();
            
            $resultado = $this->create([
                'nombre_evento' => $datos['nombre_evento'],
                'descripcion' => $datos['descripcion'] ?? '',
                'fecha_evento' => $datos['fecha_evento'],
                'mensaje_antes' => $datos['mensaje_antes'] ?? 'Próximamente',
                'mensaje_durante' => $datos['mensaje_durante'] ?? '¡El evento está en curso!',
                'mensaje_despues' => $datos['mensaje_despues'] ?? 'Evento finalizado',
                'mostrar_contador' => $datos['mostrar_contador'] ?? 1,
                'activo' => $datos['activo'] ?? 1,
                'estilo_json' => $datos['estilo_json'] ?? null,
                'estado_registro' => 'ACTIVO'
            ]);
            
            $this->commit();
            return $resultado;
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('crearEvento', $e);
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de eventos
     */
    public function getEstadisticas() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN fecha_evento >= NOW() THEN 1 ELSE 0 END) as proximos,
                    SUM(CASE WHEN fecha_evento < NOW() THEN 1 ELSE 0 END) as pasados
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
