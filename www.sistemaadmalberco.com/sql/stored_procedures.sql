USE sistema_gestion_alberco_v3;

DROP PROCEDURE IF EXISTS sp_registrar_venta;
DROP PROCEDURE IF EXISTS sp_reporte_ventas_periodo;
DROP PROCEDURE IF EXISTS sp_productos_mas_vendidos;
DROP PROCEDURE IF EXISTS sp_calcular_utilidad;

DELIMITER //

CREATE PROCEDURE sp_registrar_venta(
    IN p_id_cliente INT,
    IN p_id_usuario INT,
    IN p_id_tipo_comprobante INT,
    IN p_id_metodo_pago INT,
    IN p_referencia_pago VARCHAR(100),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_descuento DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_monto_recibido DECIMAL(10,2),
    IN p_detalle JSON,
    OUT p_id_venta INT,
    OUT p_nro_venta INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_nro_venta INT;
    DECLARE v_serie VARCHAR(10);
    DECLARE v_numero_comprobante VARCHAR(20);
    DECLARE v_correlativo INT;
    DECLARE v_vuelto DECIMAL(10,2);
    DECLARE v_id_venta INT;
    DECLARE v_i INT DEFAULT 0;
    DECLARE v_count INT;
    DECLARE v_id_producto INT;
    DECLARE v_cantidad INT;
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_detalle_subtotal DECIMAL(10,2);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 p_mensaje = MESSAGE_TEXT;
    END;

    START TRANSACTION;

    -- 1. Obtener siguiente número de venta
    SELECT COALESCE(MAX(nro_venta), 0) + 1 INTO v_nro_venta FROM tb_ventas;

    -- 2. Obtener datos del comprobante
    SELECT serie, correlativo_actual + 1 INTO v_serie, v_correlativo 
    FROM tb_tipo_comprobante WHERE id_tipo_comprobante = p_id_tipo_comprobante;
    
    SET v_numero_comprobante = LPAD(v_correlativo, 8, '0');
    SET v_vuelto = p_monto_recibido - p_total;

    -- 3. Insertar Venta
    INSERT INTO tb_ventas (
        nro_venta, serie_comprobante, numero_comprobante, id_cliente, id_usuario,
        id_tipo_comprobante, id_metodo_pago, referencia_pago, subtotal, igv,
        descuento, total, monto_recibido, vuelto, estado_venta
    ) VALUES (
        v_nro_venta, v_serie, v_numero_comprobante, p_id_cliente, p_id_usuario,
        p_id_tipo_comprobante, p_id_metodo_pago, p_referencia_pago, p_subtotal, p_igv,
        p_descuento, p_total, p_monto_recibido, v_vuelto, 'completada'
    );

    SET v_id_venta = LAST_INSERT_ID();

    -- 4. Procesar Detalles (JSON Loop)
    SET v_count = JSON_LENGTH(p_detalle);
    
    WHILE v_i < v_count DO
        SET v_id_producto = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_i, '].id_producto')));
        SET v_cantidad = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_i, '].cantidad')));
        SET v_precio = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_i, '].precio_unitario')));
        SET v_detalle_subtotal = JSON_UNQUOTE(JSON_EXTRACT(p_detalle, CONCAT('$[', v_i, '].subtotal')));

        -- Insertar Detalle
        INSERT INTO tb_detalle_ventas (
            id_venta, id_producto, cantidad, precio_unitario, subtotal
        ) VALUES (
            v_id_venta, v_id_producto, v_cantidad, v_precio, v_detalle_subtotal
        );

        -- Actualizar Stock
        UPDATE tb_almacen SET stock = stock - v_cantidad WHERE id_producto = v_id_producto;

        SET v_i = v_i + 1;
    END WHILE;

    -- 5. Actualizar Correlativo Comprobante
    UPDATE tb_tipo_comprobante SET correlativo_actual = v_correlativo WHERE id_tipo_comprobante = p_id_tipo_comprobante;

    -- 6. Movimiento de Caja (Ingreso)
    INSERT INTO tb_movimientos_caja (
        tipo_movimiento, concepto, descripcion, monto, id_usuario, id_venta, estado_movimiento
    ) VALUES (
        'ingreso', 'Venta', CONCAT('Venta #', v_nro_venta), p_total, p_id_usuario, v_id_venta, 'completado'
    );

    -- 7. Actualizar Cliente (Puntos)
    UPDATE tb_clientes 
    SET total_compras = total_compras + p_total,
        puntos_fidelidad = puntos_fidelidad + FLOOR(p_total / 10),
        ultima_compra = NOW()
    WHERE id_cliente = p_id_cliente;

    COMMIT;

    SET p_id_venta = v_id_venta;
    SET p_nro_venta = v_nro_venta;
    SET p_mensaje = 'Venta registrada correctamente';

END //

CREATE PROCEDURE sp_reporte_ventas_periodo(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        DATE(fecha_venta) as fecha,
        COUNT(*) as cantidad_ventas,
        SUM(total) as total_dia
    FROM tb_ventas
    WHERE DATE(fecha_venta) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND estado_venta = 'completada'
    GROUP BY DATE(fecha_venta)
    ORDER BY fecha;
END //

CREATE PROCEDURE sp_productos_mas_vendidos(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_limite INT
)
BEGIN
    SELECT 
        p.nombre,
        c.nombre_categoria,
        SUM(dv.cantidad) as total_vendido,
        SUM(dv.subtotal) as total_ingresos
    FROM tb_detalle_ventas dv
    JOIN tb_ventas v ON dv.id_venta = v.id_venta
    JOIN tb_almacen p ON dv.id_producto = p.id_producto
    JOIN tb_categorias c ON p.id_categoria = c.id_categoria
    WHERE DATE(v.fecha_venta) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND v.estado_venta = 'completada'
    GROUP BY p.id_producto, p.nombre, c.nombre_categoria
    ORDER BY total_vendido DESC
    LIMIT p_limite;
END //

CREATE PROCEDURE sp_calcular_utilidad(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        SUM(v.total) as total_ventas,
        SUM(dv.cantidad * p.precio_compra) as costo_total,
        SUM(v.total) - SUM(dv.cantidad * p.precio_compra) as utilidad_bruta
    FROM tb_ventas v
    JOIN tb_detalle_ventas dv ON v.id_venta = dv.id_venta
    JOIN tb_almacen p ON dv.id_producto = p.id_producto
    WHERE DATE(v.fecha_venta) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND v.estado_venta = 'completada';
END //

DELIMITER ;
