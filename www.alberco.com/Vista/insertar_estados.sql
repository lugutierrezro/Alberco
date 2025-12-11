-- =====================================================
-- INSERTAR ESTADOS REQUERIDOS PARA EL SISTEMA
-- =====================================================
-- Este script inserta los estados básicos necesarios
-- para que el sistema de pedidos funcione correctamente

INSERT INTO `tb_estados` (`id_estado`, `nombre_estado`, `descripcion`, `color`, `icono`, `orden`, `notificar_cliente`, `estado_registro`) VALUES
(1, 'Pendiente', 'Pedido recibido, esperando confirmación', '#ffc107', 'fas fa-clock', 1, 1, 'ACTIVO'),
(2, 'Confirmado', 'Pedido confirmado, en preparación', '#17a2b8', 'fas fa-check-circle', 2, 1, 'ACTIVO'),
(3, 'En Preparación', 'El pedido se está preparando en cocina', '#fd7e14', 'fas fa-fire', 3, 1, 'ACTIVO'),
(4, 'Listo para Entregar', 'Pedido listo, esperando entrega', '#28a745', 'fas fa-box', 4, 1, 'ACTIVO'),
(5, 'Entregado', 'Pedido entregado al cliente', '#28a745', 'fas fa-check-double', 5, 1, 'ACTIVO'),
(6, 'Cancelado', 'Pedido cancelado', '#dc3545', 'fas fa-times-circle', 6, 1, 'ACTIVO');

-- Verificar que se insertaron correctamente
SELECT * FROM tb_estados ORDER BY orden;
