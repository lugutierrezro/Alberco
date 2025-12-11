-- Script SQL para agregar campo de contraseña a la tabla de clientes
-- Sistema Alberco

-- Agregar campo password a tb_clientes si no existe
ALTER TABLE tb_clientes 
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL 
COMMENT 'Contraseña hasheada para autenticación de clientes'
AFTER email;

-- Crear índice en el campo password para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_cliente_password ON tb_clientes(password);

-- Comentario de la tabla actualizada
ALTER TABLE tb_clientes COMMENT = 'Tabla de clientes con autenticación';
