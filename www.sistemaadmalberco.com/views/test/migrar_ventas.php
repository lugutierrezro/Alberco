<?php
/**
 * Migración: Agregar columna id_pedido a tb_ventas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/database/config.php';
$pdo = getDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración - Agregar id_pedido</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>🔧 Migración de Base de Datos</h1>
    <p class="lead">Agregar columna <code>id_pedido</code> a la tabla <code>tb_ventas</code></p>
    
    <?php
    if (isset($_POST['ejecutar_migracion'])) {
        try {
            echo "<div class='alert alert-info'>Ejecutando migración...</div>";
            
            // Verificar si la columna ya existe
            $stmt = $pdo->query("SHOW COLUMNS FROM tb_ventas LIKE 'id_pedido'");
            $existe = $stmt->fetch();
            
            if ($existe) {
                echo "<div class='alert alert-warning'>";
                echo "<h4>⚠️ La columna 'id_pedido' ya existe</h4>";
                echo "<p>No es necesario ejecutar la migración.</p>";
                echo "</div>";
            } else {
                // Ejecutar ALTER TABLE
                $sql = "ALTER TABLE tb_ventas ADD COLUMN id_pedido INT(11) NULL AFTER id_metodo_pago";
                $pdo->exec($sql);
                
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Migración Ejecutada Exitosamente</h4>";
                echo "<p>Se agregó la columna <code>id_pedido</code> a la tabla <code>tb_ventas</code></p>";
                echo "</div>";
                
                // Agregar índice para mejorar el rendimiento
                try {
                    $pdo->exec("ALTER TABLE tb_ventas ADD INDEX idx_id_pedido (id_pedido)");
                    echo "<div class='alert alert-success'>";
                    echo "✅ Índice agregado para mejorar el rendimiento";
                    echo "</div>";
                } catch (PDOException $e) {
                    // Si el índice ya existe, ignorar
                    if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                        throw $e;
                    }
                }
                
                // Agregar foreign key
                try {
                    $pdo->exec("ALTER TABLE tb_ventas ADD CONSTRAINT fk_venta_pedido 
                               FOREIGN KEY (id_pedido) REFERENCES tb_pedidos(id_pedido) 
                               ON DELETE SET NULL ON UPDATE CASCADE");
                    echo "<div class='alert alert-success'>";
                    echo "✅ Foreign key agregado para integridad referencial";
                    echo "</div>";
                } catch (PDOException $e) {
                    // Si la foreign key ya existe o falla, continuar
                    echo "<div class='alert alert-warning'>";
                    echo "⚠️ No se pudo agregar foreign key (puede ser que ya exista o la tabla no lo soporte): " . $e->getMessage();
                    echo "</div>";
                }
            }
            
            // Verificar el resultado
            echo "<div class='card mt-4'>";
            echo "<div class='card-header bg-info text-white'><h5>Estructura Actualizada de tb_ventas</h5></div>";
            echo "<div class='card-body'>";
            
            $stmt = $pdo->query("SHOW COLUMNS FROM tb_ventas");
            $columns = $stmt->fetchAll();
            
            echo "<table class='table table-sm'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                $highlight = ($col['Field'] == 'id_pedido') ? 'table-success' : '';
                echo "<tr class='$highlight'>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div></div>";
            
            echo "<div class='alert alert-info mt-4'>";
            echo "<h5>Próximos Pasos:</h5>";
            echo "<ol>";
            echo "<li>Marca un pedido como 'Entregado'</li>";
            echo "<li>Verifica que se genere la venta automáticamente</li>";
            echo "<li>Revisa los reportes</li>";
            echo "</ol>";
            echo "<a href='test_pedido_venta.php' class='btn btn-primary'>Ir a Test de Pedido → Venta</a>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error al Ejecutar Migración</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>SQL State:</strong> " . $e->getCode() . "</p>";
            echo "</div>";
        }
    } else {
        // Mostrar formulario de confirmación
        ?>
        <div class="card">
            <div class="card-header bg-warning">
                <h4>⚠️ Confirmar Migración</h4>
            </div>
            <div class="card-body">
                <p>Esta migración ejecutará el siguiente comando SQL:</p>
                <pre class="bg-light p-3 rounded"><code>ALTER TABLE tb_ventas ADD COLUMN id_pedido INT(11) NULL AFTER id_metodo_pago;</code></pre>
                
                <div class="alert alert-info">
                    <strong>¿Qué hace esto?</strong>
                    <ul>
                        <li>Agrega la columna <code>id_pedido</code> a la tabla <code>tb_ventas</code></li>
                        <li>Permite vincular cada venta con su pedido original</li>
                        <li>Es necesario para la generación automática de ventas</li>
                        <li>No afecta los datos existentes</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <button type="submit" name="ejecutar_migracion" class="btn btn-success btn-lg">
                        <i class="fas fa-database"></i> Ejecutar Migración
                    </button>
                    <a href="../../dashboard.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
        <?php
    }
    ?>
</div>
</body>
</html>
