<?php
/**
 * Servicio de Autenticación para Clientes
 * Sistema Alberco - Portal de Clientes
 * VERSIÓN SIN MODIFICACIÓN DE BASE DE DATOS
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/init.php';

class AuthCliente {
    private $clienteModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
    }
    
    /**
     * Registrar nuevo cliente (sin contraseña en BD)
     * @param array $datos
     * @return array
     */
    public function registrar($datos) {
        try {
            // Validar datos requeridos (sin password)
            if (empty($datos['nombre']) || empty($datos['telefono'])) {
                return [
                    'success' => false,
                    'mensaje' => 'Nombre y teléfono son requeridos'
                ];
            }
            
            // Validar formato de teléfono (9 dígitos)
            if (!preg_match('/^[0-9]{9}$/', $datos['telefono'])) {
                return [
                    'success' => false,
                    'mensaje' => 'El teléfono debe tener 9 dígitos'
                ];
            }
            
            // Validar que el teléfono no esté registrado
            $clienteExistente = $this->clienteModel->getByTelefono($datos['telefono']);
            if ($clienteExistente) {
                // Si ya existe, iniciar sesión directamente
                $this->iniciarSesion($clienteExistente);
                return [
                    'success' => true,
                    'mensaje' => '¡Bienvenido de nuevo!',
                    'cliente_id' => $clienteExistente['id_cliente']
                ];
            }
            
            // Validar email si se proporciona
            if (!empty($datos['email'])) {
                if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                    return [
                        'success' => false,
                        'mensaje' => 'El email no es válido'
                    ];
                }
            }
            
            // Validar si el DNI ya existe
            if (!empty($datos['numero_documento'])) {
                if ($this->clienteModel->getByDocumento($datos['numero_documento'])) {
                    return [
                        'success' => false,
                        'mensaje' => 'El documento ya está registrado'
                    ];
                }
            }

            // Validar si el email ya existe (búsqueda manual ya que no hay método directo getByEmail publico, usamos search o asumimos catch error, mejor search)
            // Nota: El modelo no tiene getByEmail especifico, pero search busca en email.
            // Para ser seguros y eficientes, mejor confiamos en el catch de duplicados, 
            // PERO ya que estamos editando, agreguemos la validación de indices abajo y mejora de mensaje.
            
            // Preparar datos del cliente (SIN password)
            // Generar código de cliente único
            $codigoCliente = 'CLI-' . rand(100000, 999999);
            
            // Preparar datos del cliente (SIN password)
            $clienteData = [
                'codigo_cliente' => $codigoCliente,
                'nombre' => trim($datos['nombre']),
                'apellidos' => trim($datos['apellidos'] ?? ''),
                'tipo_documento' => $datos['tipo_documento'] ?? 'DNI',
                'numero_documento' => !empty($datos['numero_documento']) ? trim($datos['numero_documento']) : null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'telefono' => $datos['telefono'],
                'email' => !empty($datos['email']) ? trim($datos['email']) : null,
                'direccion' => trim($datos['direccion'] ?? ''),
                'distrito' => trim($datos['distrito'] ?? ''),
                'ciudad' => trim($datos['ciudad'] ?? ''),
                'tipo_cliente' => 'NUEVO',
                'estado_registro' => 'ACTIVO',
                'fyh_creacion' => date('Y-m-d H:i:s')
            ];
            
            // Crear cliente
            $clienteId = $this->clienteModel->create($clienteData);
            
            if ($clienteId) {
                // Obtener datos del cliente creado
                $cliente = $this->clienteModel->getById($clienteId);
                
                // Iniciar sesión automáticamente
                $this->iniciarSesion($cliente);
                
                return [
                    'success' => true,
                    'mensaje' => '¡Registro exitoso! Bienvenido a Alberco',
                    'cliente_id' => $clienteId
                ];
            } else {
                return [
                    'success' => false,
                    'mensaje' => 'Error al crear la cuenta. Intenta nuevamente'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en registro de cliente: " . $e->getMessage());
            
            // Mensajes amigables para errores comunes de BD
            $mensaje = 'Error del sistema. Por favor intenta más tarde';
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'numero_documento') !== false) {
                    $mensaje = 'El número de documento ya está registrado.';
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    $mensaje = 'El correo electrónico ya está registrado.';
                } else {
                    $mensaje = 'Ya existe una cuenta con estos datos (DNI o Correo duplicado).';
                }
            } else {
                 $mensaje .= ' (' . $e->getMessage() . ')'; // Mantener debug info por ahora
            }

            return [
                'success' => false,
                'mensaje' => $mensaje
            ];
        }
    }
    
    /**
     * Iniciar sesión (solo con teléfono, sin contraseña)
     * @param string $telefono
     * @param string $password (ignorado)
     * @return array
     */
    public function login($telefono, $password = null) {
        try {
            // Validar datos (solo teléfono)
            if (empty($telefono)) {
                return [
                    'success' => false,
                    'mensaje' => 'El teléfono es requerido'
                ];
            }
            
            // Buscar cliente por teléfono
            $cliente = $this->clienteModel->getByTelefono($telefono);
            
            if (!$cliente) {
                return [
                    'success' => false,
                    'mensaje' => 'No existe una cuenta con este teléfono. Por favor regístrate primero.'
                ];
            }
            
            // Verificar que la cuenta esté activa
            if ($cliente['estado_registro'] !== 'ACTIVO') {
                return [
                    'success' => false,
                    'mensaje' => 'Tu cuenta está inactiva. Contacta con soporte'
                ];
            }
            
            // Iniciar sesión directamente (sin verificar contraseña)
            $this->iniciarSesion($cliente);
            
            return [
                'success' => true,
                'mensaje' => '¡Bienvenido de nuevo!',
                'cliente' => [
                    'id' => $cliente['id_cliente'],
                    'nombre' => $cliente['nombre'],
                    'tipo' => $cliente['tipo_cliente']
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error en login de cliente: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error del sistema. Por favor intenta más tarde'
            ];
        }
    }
    
    /**
     * Iniciar sesión del cliente
     * @param array $cliente
     */
    private function iniciarSesion($cliente) {
        $_SESSION['cliente_logueado'] = true;
        $_SESSION['cliente_id'] = $cliente['id_cliente'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'];
        $_SESSION['cliente_apellidos'] = $cliente['apellidos'] ?? '';
        $_SESSION['cliente_telefono'] = $cliente['telefono'];
        $_SESSION['cliente_email'] = $cliente['email'] ?? '';
        $_SESSION['cliente_tipo'] = $cliente['tipo_cliente'];
        $_SESSION['cliente_puntos'] = $cliente['puntos_fidelidad'] ?? 0;
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        // Limpiar variables de sesión del cliente
        unset($_SESSION['cliente_logueado']);
        unset($_SESSION['cliente_id']);
        unset($_SESSION['cliente_nombre']);
        unset($_SESSION['cliente_apellidos']);
        unset($_SESSION['cliente_telefono']);
        unset($_SESSION['cliente_email']);
        unset($_SESSION['cliente_tipo']);
        unset($_SESSION['cliente_puntos']);
        
        return [
            'success' => true,
            'mensaje' => 'Sesión cerrada correctamente'
        ];
    }
    
    /**
     * Verificar si hay sesión activa
     * @return bool
     */
    public function estaLogueado() {
        return isset($_SESSION['cliente_logueado']) && $_SESSION['cliente_logueado'] === true;
    }
    
    /**
     * Obtener ID del cliente logueado
     * @return int|null
     */
    public function getClienteId() {
        return $_SESSION['cliente_id'] ?? null;
    }
    
    /**
     * Obtener datos del cliente logueado
     * @return array|null
     */
    public function getClienteActual() {
        if (!$this->estaLogueado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['cliente_id'],
            'nombre' => $_SESSION['cliente_nombre'],
            'apellidos' => $_SESSION['cliente_apellidos'] ?? '',
            'telefono' => $_SESSION['cliente_telefono'],
            'email' => $_SESSION['cliente_email'] ?? '',
            'tipo' => $_SESSION['cliente_tipo'],
            'puntos' => $_SESSION['cliente_puntos'] ?? 0
        ];
    }
    
    /**
     * Actualizar puntos en sesión
     * @param int $puntos
     */
    public function actualizarPuntosSesion($puntos) {
        if ($this->estaLogueado()) {
            $_SESSION['cliente_puntos'] = $puntos;
        }
    }
    
    /**
     * Requerir autenticación (redirige si no está logueado)
     * @param string $redirectUrl
     */
    public function requerirAuth($redirectUrl = 'login_cliente.php') {
        if (!$this->estaLogueado()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
}

// Función helper global
function getAuthCliente() {
    static $auth = null;
    if ($auth === null) {
        $auth = new AuthCliente();
    }
    return $auth;
}

function clienteLogueado() {
    return getAuthCliente()->estaLogueado();
}

function getClienteActual() {
    return getAuthCliente()->getClienteActual();
}
