<?php
/**
 * API de Direcciones del Cliente
 * Permite gestionar direcciones guardadas para pedidos rápidos
 */

require_once __DIR__ . '/../app/init.php';
require_once __DIR__ . '/auth_cliente.php';

header('Content-Type: application/json');

// Verificar autenticación
$auth = getAuthCliente();
if (!$auth->estaLogueado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$clienteActual = $auth->getClienteActual();
$idCliente = $clienteActual['id'];

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    $pdo = getDB();
    
    switch ($accion) {
        case 'listar':
            // Obtener direcciones del cliente
            $stmt = $pdo->prepare("
                SELECT * FROM tb_direcciones_cliente 
                WHERE id_cliente = ? AND estado_registro = 'ACTIVO'
                ORDER BY es_principal DESC, fyh_creacion DESC
            ");
            $stmt->execute([$idCliente]);
            $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'direcciones' => $direcciones
            ]);
            break;
            
        case 'guardar':
            $direccion = $_POST['direccion'] ?? '';
            $referencia = $_POST['referencia'] ?? '';
            $distrito = $_POST['distrito'] ?? '';
            $esPrincipal = isset($_POST['es_principal']) ? 1 : 0;
            
            if (empty($direccion) || empty($distrito)) {
                throw new Exception('Dirección y distrito son obligatorios');
            }
            
            // Si es principal, quitar principal de otras
            if ($esPrincipal) {
                $stmt = $pdo->prepare("UPDATE tb_direcciones_cliente SET es_principal = 0 WHERE id_cliente = ?");
                $stmt->execute([$idCliente]);
            }
            
            // Insertar nueva dirección
            $stmt = $pdo->prepare("
                INSERT INTO tb_direcciones_cliente 
                (id_cliente, direccion, referencia, distrito, es_principal, fyh_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$idCliente, $direccion, $referencia, $distrito, $esPrincipal]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Dirección guardada correctamente',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        case 'eliminar':
            $idDireccion = $_POST['id'] ?? 0;
            
            $stmt = $pdo->prepare("
                UPDATE tb_direcciones_cliente 
                SET estado_registro = 'INACTIVO' 
                WHERE id_direccion = ? AND id_cliente = ?
            ");
            $stmt->execute([$idDireccion, $idCliente]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Dirección eliminada'
            ]);
            break;
            
        case 'marcar_principal':
            $idDireccion = $_POST['id'] ?? 0;
            
            // Quitar principal de todas
            $stmt = $pdo->prepare("UPDATE tb_direcciones_cliente SET es_principal = 0 WHERE id_cliente = ?");
            $stmt->execute([$idCliente]);
            
            // Marcar como principal
            $stmt = $pdo->prepare("UPDATE tb_direcciones_cliente SET es_principal = 1 WHERE id_direccion = ? AND id_cliente = ?");
            $stmt->execute([$idDireccion, $idCliente]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Dirección principal actualizada'
            ]);
            break;
            
        case 'calcular_delivery':
            $distrito = $_POST['distrito'] ?? '';
            
            // Tarifas por distrito
            $tarifas = [
                'ate' => 5.00,
                'santa anita' => 5.00,
                'la molina' => 7.00,
                'san luis' => 6.00,
                'el agustino' => 5.00,
                'san juan de lurigancho' => 8.00,
                'lurigancho' => 8.00,
                'chaclacayo' => 10.00,
                'lima' => 6.00,
                'cercado de lima' => 6.00
            ];
            
            $distritoNorm = strtolower(trim($distrito));
            $costo = $tarifas[$distritoNorm] ?? 10.00; // Default 10 soles
            
            echo json_encode([
                'success' => true,
                'costo_delivery' => $costo,
                'distrito' => $distrito
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
