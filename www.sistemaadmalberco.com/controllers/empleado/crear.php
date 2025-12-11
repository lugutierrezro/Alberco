<?php
// Crear Empleado (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

// Funciones auxiliares
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

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
    $data = [
        'codigo_empleado' => sanitize($_POST['codigo_empleado'] ?? ''),
        'nombres' => sanitize($_POST['nombres'] ?? ''),
        'apellidos' => sanitize($_POST['apellidos'] ?? ''),
        'tipo_documento' => sanitize($_POST['tipo_documento'] ?? 'DNI'),
        'numero_documento' => sanitize($_POST['numero_documento'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'telefono' => sanitize($_POST['telefono'] ?? ''),
        'celular' => sanitize($_POST['celular'] ?? ''),
        'direccion' => sanitize($_POST['direccion'] ?? ''),
        'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
        'fecha_contratacion' => $_POST['fecha_contratacion'] ?? date('Y-m-d'),
        'id_rol' => (int)($_POST['id_rol'] ?? 0), // ✅ AGREGADO
        'salario' => !empty($_POST['salario']) ? (float)$_POST['salario'] : null,
        'turno' => sanitize($_POST['turno'] ?? 'ROTATIVO')
    ];
    
    // Validaciones
    if (empty($data['codigo_empleado']) || empty($data['nombres']) || 
        empty($data['apellidos']) || empty($data['numero_documento']) || 
        empty($data['email']) || empty($data['id_rol'])) { // ✅ Agregada validación de id_rol
        $_SESSION['error'] = 'Complete todos los campos obligatorios';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    if (!validarEmail($data['email'])) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    // ✅ Verificar que el rol existe
    $checkRolSql = "SELECT id_rol FROM tb_roles WHERE id_rol = ? AND estado_registro = 'ACTIVO'";
    $checkRolStmt = $pdo->prepare($checkRolSql);
    $checkRolStmt->execute([$data['id_rol']]);
    
    if (!$checkRolStmt->fetch()) {
        $_SESSION['error'] = 'El rol seleccionado no es válido';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    // Verificar código duplicado
    $checkCodigoSql = "SELECT id_empleado FROM tb_empleados WHERE codigo_empleado = ?";
    $checkCodigoStmt = $pdo->prepare($checkCodigoSql);
    $checkCodigoStmt->execute([$data['codigo_empleado']]);
    
    if ($checkCodigoStmt->fetch()) {
        $_SESSION['error'] = 'El código de empleado ya existe';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    // Verificar documento duplicado
    $checkDocSql = "SELECT id_empleado FROM tb_empleados WHERE numero_documento = ?";
    $checkDocStmt = $pdo->prepare($checkDocSql);
    $checkDocStmt->execute([$data['numero_documento']]);
    
    if ($checkDocStmt->fetch()) {
        $_SESSION['error'] = 'El número de documento ya está registrado';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    // ✅ Verificar email duplicado
    $checkEmailSql = "SELECT id_empleado FROM tb_empleados WHERE email = ?";
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->execute([$data['email']]);
    
    if ($checkEmailStmt->fetch()) {
        $_SESSION['error'] = 'El email ya está registrado';
        header('Location: ' . URL_BASE . '/views/empleado/create.php');
        exit;
    }
    
    // Manejar foto
    $fotoPath = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/empleados/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $filename = 'EMP_' . $data['codigo_empleado'] . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $filepath)) {
                $fotoPath = 'uploads/empleados/' . $filename;
            }
        }
    }
    
    // ✅ Insertar empleado (CORREGIDO - incluye id_rol)
    $insertSql = "INSERT INTO tb_empleados 
                  (codigo_empleado, nombres, apellidos, tipo_documento, numero_documento,
                   email, telefono, celular, direccion, fecha_nacimiento, fecha_contratacion,
                   id_rol, salario, turno, foto, estado_laboral, estado_registro, fyh_creacion)
                  VALUES 
                  (:codigo, :nombres, :apellidos, :tipo_doc, :num_doc,
                   :email, :telefono, :celular, :direccion, :fecha_nac, :fecha_cont,
                   :id_rol, :salario, :turno, :foto, 'ACTIVO', 'ACTIVO', NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':codigo' => $data['codigo_empleado'],
        ':nombres' => $data['nombres'],
        ':apellidos' => $data['apellidos'],
        ':tipo_doc' => $data['tipo_documento'],
        ':num_doc' => $data['numero_documento'],
        ':email' => $data['email'],
        ':telefono' => $data['telefono'],
        ':celular' => $data['celular'],
        ':direccion' => $data['direccion'],
        ':fecha_nac' => $data['fecha_nacimiento'],
        ':fecha_cont' => $data['fecha_contratacion'],
        ':id_rol' => $data['id_rol'], // ✅ AGREGADO
        ':salario' => $data['salario'],
        ':turno' => $data['turno'],
        ':foto' => $fotoPath
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Empleado creado correctamente';
    } else {
        $_SESSION['error'] = 'Error al crear el empleado';
    }
    
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al crear empleado: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/empleado/create.php');
    exit;
}
