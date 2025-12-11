<?php
/**
 * Helper para registrar actividades en el sistema
 * Utiliza la tabla tb_auditoria mejorada
 */

/**
 * Registrar una actividad en el sistema
 * 
 * @param string $modulo Módulo del sistema (productos, ventas, pedidos, etc.)
 * @param string $accion Acción realizada (INSERT, UPDATE, DELETE, LOGIN, VIEW, etc.)
 * @param string $descripcion Descripción legible de la acción
 * @param string|null $tabla_afectada Tabla afectada (opcional)
 * @param int|null $id_registro_afectado ID del registro afectado (opcional)
 * @param array|null $datos_anteriores Datos antes del cambio (opcional)
 * @param array|null $datos_nuevos Datos después del cambio (opcional)
 * @param string $nivel Nivel de importancia (info, warning, error, critical)
 * @return bool
 */
function registrarActividad(
    $modulo,
    $accion,
    $descripcion,
    $tabla_afectada = null,
    $id_registro_afectado = null,
    $datos_anteriores = null,
    $datos_nuevos = null,
    $nivel = 'info'
) {
    try {
        // Obtener conexión
        global $pdo;
        if (!isset($pdo)) {
            require_once __DIR__ . '/../services/database/config.php';
        }
        
        // Obtener datos del usuario actual
        $id_usuario = $_SESSION['sesion_id_usuario'] ?? null;
        
        // Obtener IP y user agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Convertir arrays a JSON si existen
        $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores) : null;
        $datos_nuevos_json = $datos_nuevos ? json_encode($datos_nuevos) : null;
        
        // Preparar consulta
        $sql = "INSERT INTO tb_auditoria (
            tabla_afectada,
            id_registro_afectado,
            accion,
            modulo,
            descripcion,
            nivel,
            datos_anteriores,
            datos_nuevos,
            id_usuario,
            ip_address,
            user_agent
        ) VALUES (
            :tabla_afectada,
            :id_registro_afectado,
            :accion,
            :modulo,
            :descripcion,
            :nivel,
            :datos_anteriores,
            :datos_nuevos,
            :id_usuario,
            :ip_address,
            :user_agent
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tabla_afectada' => $tabla_afectada,
            ':id_registro_afectado' => $id_registro_afectado,
            ':accion' => $accion,
            ':modulo' => $modulo,
            ':descripcion' => $descripcion,
            ':nivel' => $nivel,
            ':datos_anteriores' => $datos_anteriores_json,
            ':datos_nuevos' => $datos_nuevos_json,
            ':id_usuario' => $id_usuario,
            ':ip_address' => $ip_address,
            ':user_agent' => $user_agent
        ]);
        
        return true;
        
    } catch (Exception $e) {
        // Log silencioso para no interrumpir el flujo normal
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

/**
 * Registrar actividad de login
 */
function registrarLogin($username, $exitoso = true) {
    $descripcion = $exitoso 
        ? "Usuario '$username' inició sesión exitosamente"
        : "Intento fallido de inicio de sesión para '$username'";
    
    $nivel = $exitoso ? 'info' : 'warning';
    
    return registrarActividad(
        'usuarios',
        'LOGIN',
        $descripcion,
        'tb_usuarios',
        null,
        null,
        ['username' => $username, 'exitoso' => $exitoso],
        $nivel
    );
}

/**
 * Registrar actividad de logout
 */
function registrarLogout($username) {
    return registrarActividad(
        'usuarios',
        'LOGOUT',
        "Usuario '$username' cerró sesión",
        'tb_usuarios',
        null,
        null,
        ['username' => $username]
    );
}

/**
 * Registrar creación de registro
 */
function registrarCreacion($modulo, $tabla, $id_registro, $descripcion, $datos = []) {
    return registrarActividad(
        $modulo,
        'INSERT',
        $descripcion,
        $tabla,
        $id_registro,
        null,
        $datos
    );
}

/**
 * Registrar actualización de registro
 */
function registrarActualizacion($modulo, $tabla, $id_registro, $descripcion, $datos_anteriores = [], $datos_nuevos = []) {
    return registrarActividad(
        $modulo,
        'UPDATE',
        $descripcion,
        $tabla,
        $id_registro,
        $datos_anteriores,
        $datos_nuevos
    );
}

/**
 * Registrar eliminación de registro
 */
function registrarEliminacion($modulo, $tabla, $id_registro, $descripcion, $datos = []) {
    return registrarActividad(
        $modulo,
        'DELETE',
        $descripcion,
        $tabla,
        $id_registro,
        $datos,
        null,
        'warning'
    );
}

/**
 * Registrar error del sistema
 */
function registrarError($modulo, $descripcion, $detalles = []) {
    return registrarActividad(
        $modulo,
        'ERROR',
        $descripcion,
        null,
        null,
        null,
        $detalles,
        'error'
    );
}

/**
 * Registrar acción crítica
 */
function registrarAccionCritica($modulo, $accion, $descripcion, $datos = []) {
    return registrarActividad(
        $modulo,
        $accion,
        $descripcion,
        null,
        null,
        null,
        $datos,
        'critical'
    );
}
