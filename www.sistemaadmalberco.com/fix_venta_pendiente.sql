-- ===============================================
-- ACTUALIZAR VENTA PENDIENTE A COMPLETADA
-- ===============================================
-- Actualiza la venta #8 que quedó pendiente

UPDATE tb_ventas 
SET estado_venta = 'completada'
WHERE id_venta = 8 AND estado_venta = 'pendiente';

-- Verificar
SELECT 
    id_venta,
    nro_venta,
    id_pedido,
    estado_venta,
    total
FROM tb_ventas 
WHERE id_venta = 8;
