// Validaciones básicas
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmar acciones de eliminación/baja
    var btnsBaja = document.querySelectorAll('.btn-baja, .btn-danger');
    btnsBaja.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if(!confirm('¿Está seguro de realizar esta acción?')) {
                e.preventDefault();
            }
        });
    });
    
    // Validar formulario de empleados
    var formEmpleado = document.querySelector('form');
    if(formEmpleado) {
        formEmpleado.addEventListener('submit', function(e) {
            var salario = document.querySelector('input[name="salario_base"]');
            if(salario && parseFloat(salario.value) <= 0) {
                alert('El salario debe ser mayor a 0');
                e.preventDefault();
            }
        });
    }
    
    console.log('Sistema RRHH cargado');
});