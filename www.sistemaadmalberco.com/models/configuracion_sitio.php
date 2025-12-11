<?php
/**
 * Modelo ConfiguracionSitio
 * Gestiona la configuración dinámica del sitio web www.alberco.com
 */

require_once __DIR__ . '/database.php';

class ConfiguracionSitio extends BaseModel {
    protected $table = 'tb_configuracion_sitio';
    protected $primaryKey = 'id_config';
    
    
    /**
     * Obtener todos los registros (sobrescrito para usar columna correcta)
     */
    public function getAll($includeInactive = false) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!$includeInactive) {
                $sql .= " WHERE estado_registro = 'ACTIVO'";
            }
            
            $sql .= " ORDER BY categoria, clave ASC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getAll', $e);
            return [];
        }
    }
    
    /**
     * Obtener configuración por clave
     */
    public function getByClave($clave) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE clave = :clave 
                    AND estado_registro = 'ACTIVO' 
                    LIMIT 1";
            
            $result = $this->query($sql, [':clave' => $clave]);
            return !empty($result) ? $result[0] : null;
        } catch(PDOException $e) {
            $this->logError('getByClave', $e);
            return null;
        }
    }
    
    /**
     * Obtener todas las configuraciones por categoría
     */
    public function getByCategoria($categoria = null) {
        try {
            if ($categoria) {
                $sql = "SELECT * FROM {$this->table} 
                        WHERE categoria = :categoria 
                        AND estado_registro = 'ACTIVO' 
                        ORDER BY clave ASC";
                return $this->query($sql, [':categoria' => $categoria]);
            } else {
                $sql = "SELECT * FROM {$this->table} 
                        WHERE estado_registro = 'ACTIVO' 
                        ORDER BY categoria, clave ASC";
                return $this->query($sql);
            }
        } catch(PDOException $e) {
            $this->logError('getByCategoria', $e);
            return [];
        }
    }
    
    /**
     * Guardar o actualizar configuración
     */
    public function guardarConfiguracion($clave, $valor, $tipo_dato = 'texto', $categoria = 'general', $descripcion = '', $idUsuario = null) {
        try {
            $this->beginTransaction();
            
            // Verificar si ya existe
            $existente = $this->getByClave($clave);
            
            if ($existente) {
                // Actualizar
                $sql = "UPDATE {$this->table} SET 
                        valor = :valor,
                        tipo_dato = :tipo_dato,
                        categoria = :categoria,
                        descripcion = :descripcion,
                        id_usuario_modifico = :id_usuario
                        WHERE clave = :clave";
                
                $this->execute($sql, [
                    ':valor' => $valor,
                    ':tipo_dato' => $tipo_dato,
                    ':categoria' => $categoria,
                    ':descripcion' => $descripcion,
                    ':id_usuario' => $idUsuario,
                    ':clave' => $clave
                ]);
                
                $result = $existente['id_config'];
            } else {
                // Insertar
                $result = $this->create([
                    'clave' => $clave,
                    'valor' => $valor,
                    'tipo_dato' => $tipo_dato,
                    'categoria' => $categoria,
                    'descripcion' => $descripcion,
                    'id_usuario_modifico' => $idUsuario,
                    'estado_registro' => 'ACTIVO'
                ]);
            }
            
            $this->commit();
            return $result;
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('guardarConfiguracion', $e);
            return false;
        }
    }
    
    /**
     * Obtener todas las configuraciones como array asociativo
     */
    public function getAllAsArray() {
        try {
            $configs = $this->query("SELECT clave, valor, tipo_dato FROM {$this->table} WHERE estado_registro = 'ACTIVO'");
            $result = [];
            
            foreach ($configs as $config) {
                $valor = $config['valor'];
                
                // Convertir según tipo de dato
                switch ($config['tipo_dato']) {
                    case 'numero':
                        $valor = is_numeric($valor) ? (float)$valor : 0;
                        break;
                    case 'booleano':
                        $valor = filter_var($valor, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'json':
                        $valor = json_decode($valor, true);
                        break;
                }
                
                $result[$config['clave']] = $valor;
            }
            
            return $result;
        } catch(PDOException $e) {
            $this->logError('getAllAsArray', $e);
            return [];
        }
    }
    
    /**
     * Guardar múltiples configuraciones
     */
    public function guardarMultiples($configuraciones, $idUsuario = null) {
        try {
            $errores = [];
            $exitos = 0;
            
            foreach ($configuraciones as $clave => $datos) {
                $resultado = $this->guardarConfiguracion(
                    $clave,
                    $datos['valor'] ?? '',
                    $datos['tipo_dato'] ?? 'texto',
                    $datos['categoria'] ?? 'general',
                    $datos['descripcion'] ?? '',
                    $idUsuario
                );
                
                if ($resultado) {
                    $exitos++;
                } else {
                    $errores[] = $clave;
                }
            }
            
            // Retornar true si al menos uno fue exitoso
            return $exitos > 0;
            
        } catch(Exception $e) {
            $this->logError('guardarMultiples', $e);
            return false;
        }
    }
    
    /**
     * Obtener categorías disponibles
     */
    public function getCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM {$this->table} 
                    WHERE estado_registro = 'ACTIVO' 
                    ORDER BY categoria ASC";
            
            $result = $this->query($sql);
            return array_column($result, 'categoria');
        } catch(PDOException $e) {
            $this->logError('getCategorias', $e);
            return [];
        }
    }
}
