<?php
// Actualizar Empleado (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
}

try {
    $empleadoId = (int)($_POST['id_empleado'] ?? 0);
    
    $data = [
        'nombres' => sanitize($_POST['nombres'] ?? ''),
        'apellidos' => sanitize($_POST['apellidos'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'telefono' => sanitize($_POST['telefono'] ?? ''),
        'celular' => sanitize($_POST['celular'] ?? ''),
        'direccion' => sanitize($_POST['direccion'] ?? ''),
        'salario' => !empty($_POST['salario']) ? (float)$_POST['salario'] : null,
        'turno' => sanitize($_POST['turno'] ?? 'ROTATIVO'),
        'estado_laboral' => sanitize($_POST['estado_laboral'] ?? 'ACTIVO')
    ];
    
    // Validaciones
    if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['email'])) {
        $_SESSION['error'] = 'Complete todos los campos obligatorios';
        header('Location: ' . URL_BASE . '/views/empleado//update.php?id=' . $empleadoId);
        exit;
    }
    
    if (!validarEmail($data['email'])) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/empleado/update.php?id=' . $empleadoId);
        exit;
    }
    
    // Obtener datos actuales
    $getSql = "SELECT codigo_empleado, foto FROM tb_empleados WHERE id_empleado = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$empleadoId]);
    $empleadoActual = $getStmt->fetch();
    
    if (!$empleadoActual) {
        $_SESSION['error'] = 'Empleado no encontrado';
        header('Location: ' . URL_BASE . '/views/empleado/');
        exit;
    }
    
    // Manejar foto nueva
    $fotoPath = $empleadoActual['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/empleados/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $filename = 'EMP_' . $empleadoActual['codigo_empleado'] . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $filepath)) {
                // Eliminar foto anterior
                if (!empty($empleadoActual['foto'])) {
                    $oldFile = dirname(__DIR__, 2) . '/' . $empleadoActual['foto'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $fotoPath = 'uploads/empleados/' . $filename;
            }
        }
    }
    
    // Actualizar
    $updateSql = "UPDATE tb_empleados SET
                  nombres = :nombres,
                  apellidos = :apellidos,
                  email = :email,
                  telefono = :telefono,
                  celular = :celular,
                  direccion = :direccion,
                  salario = :salario,
                  turno = :turno,
                  estado_laboral = :estado,
                  foto = :foto,
                  fyh_actualizacion = NOW()
                  WHERE id_empleado = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':nombres' => $data['nombres'],
        ':apellidos' => $data['apellidos'],
        ':email' => $data['email'],
        ':telefono' => $data['telefono'],
        ':celular' => $data['celular'],
        ':direccion' => $data['direccion'],
        ':salario' => $data['salario'],
        ':turno' => $data['turno'],
        ':estado' => $data['estado_laboral'],
        ':foto' => $fotoPath,
        ':id' => $empleadoId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Empleado actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el empleado';
    }
    
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar empleado: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
}
