# Solución: PHP no se está ejecutando

## ❌ Problema Identificado

El navegador muestra el código PHP en lugar de ejecutarlo. Esto indica que Apache no está procesando archivos PHP.

## ✅ Soluciones (en orden):

### Solución 1: Reiniciar Apache en XAMPP

1. Abre el **Panel de Control de XAMPP**
2. **STOP** el módulo Apache (botón rojo)
3. Espera 5 segundos
4. **START** el módulo Apache (botón verde)
5. Verifica que diga "Running" en verde

### Solución 2: Verificar que sea localhost, no 127.0.0.1

En tu navegador, asegúrate de usar:
- ✅ `http://localhost/www.alberco.com/debug_pedidos.php`
- ❌ NO uses `http://127.0.0.1/...`

### Solución 3: Verificar puerto de Apache

1. En XAMPP Control Panel, haz clic en **Config** (botón a la derecha de Apache)
2. Selecciona **Apache (httpd.conf)**
3. Busca la línea que dice: `Listen 80`
4. Si dice otro puerto (ej: `Listen 8080`), anótalo
5. Tu URL sería: `http://localhost:8080/www.alberco.com/debug_pedidos.php`

### Solución 4: Probar con archivo PHP simple

Crea este archivo: `c:\xampp\htdocs\test.php`

```php
<?php
phpinfo();
?>
```

Luego abre: `http://localhost/test.php`

- ✅ Si ves una página con información de PHP = PHP funciona
- ❌ Si ves el código `<?php phpinfo(); ?>` = PHP NO funciona

## 🎯 Siguiente paso

Después de reiniciar Apache, prueba:

```
http://localhost/www.alberco.com/Vista/menu.php
```

Y haz un pedido de prueba.
