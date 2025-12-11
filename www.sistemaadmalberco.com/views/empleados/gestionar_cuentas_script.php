

<!-- Formulario oculto para crear cuenta -->
<form id="formCrearCuenta" action="../../controllers/empleados/crear_cuenta.php" method="POST" style="display:none;">
    <input type="hidden" name="id_empleado" id="id_empleado_crear">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
function confirmarCrearCuenta(idEmpleado, nombreCompleto, codigoEmp, email, documento, rol) {
    Swal.fire({
        title: '¿Crear Cuenta de Usuario?',
        html: `
            <div class="text-left">
                <h5 class="mb-3"><i class="fas fa-user-plus text-success"></i> Datos del Empleado</h5>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Nombre Completo:</th>
                        <td><strong>${nombreCompleto}</strong></td>
                    </tr>
                    <tr>
                        <th>Código Empleado:</th>
                        <td><code class="bg-light p-1">${codigoEmp}</code></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><i class="fas fa-envelope text-info"></i> ${email}</td>
                    </tr>
                    <tr>
                        <th>Rol:</th>
                        <td><span class="badge badge-info">${rol}</span></td>
                    </tr>
                    <tr>
                        <th>Documento:</th>
                        <td>${documento}</td>
                    </tr>
                </table>

                <div class="alert alert-info mb-0 mt-3">
                    <h6><i class="fas fa-key"></i> Credenciales que se generarán:</h6>
                    <ul class="mb-0">
                        <li><strong>Username:</strong> <code>emp_${codigoEmp.toLowerCase()}</code></li>
                        <li><strong>Password:</strong> <code>${documento}</code> (número de documento)</li>
                        <li><strong>Email vinculado:</strong> ${email}</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'question',
        width: '600px',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, Crear Cuenta',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            document.getElementById('id_empleado_crear').value = idEmpleado;
            document.getElementById('formCrearCuenta').submit();
        }
    });
}
</script>
