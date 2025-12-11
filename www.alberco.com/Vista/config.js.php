<?php
/**
 * Config Helper - Exportar configuración para JavaScript
 * Este archivo expone las constantes de configuración del servidor al frontend
 */

require_once __DIR__ . '/../app/init.php';

header('Content-Type: application/javascript');
header('Cache-Control: no-cache');

// Exportar URL base como variable JavaScript
?>
const APP_CONFIG = {
    SALES_URL_BASE: '<?= SALES_URL_BASE ?>',
    ADMIN_URL_BASE: '<?= URL_BASE ?>',
    IS_PRODUCTION: <?= ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') ? 'true' : 'false' ?>,
    ENVIRONMENT: '<?= ENVIRONMENT ?>'
};
