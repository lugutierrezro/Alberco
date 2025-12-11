<?php include 'header.php'; ?>

<h2>Realizar Pedido</h2>

<form id="formPedido">
    <label for="direccion">Dirección de Entrega:</label><br>
    <input type="text" id="direccion" name="direccion" required><br><br>

    <label>Productos:</label><br>
    <div id="productosContainer">
        <!-- Aquí se deben cargar dinámicamente los productos disponibles -->
        <div>
            <input type="checkbox" name="producto[]" value="1" data-precio="15.00" data-nombre="Pollo a la Brasa">
            Pollo a la Brasa - S/15.00 
            Cantidad: <input type="number" name="cantidad_1" min="1" value="1" style="width: 50px;">
        </div>
        <div>
            <input type="checkbox" name="producto[]" value="2" data-precio="20.00" data-nombre="Arroz Chaufa">
            Arroz Chaufa - S/20.00 
            Cantidad: <input type="number" name="cantidad_2" min="1" value="1" style="width: 50px;">
        </div>
    </div><br>

    <button type="submit">Enviar Pedido</button>
</form>

<div id="mensaje"></div>

<?php include 'footer.php'; ?>
