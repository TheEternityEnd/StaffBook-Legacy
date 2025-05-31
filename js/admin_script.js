// ===================== admin.php ===================== \\
// Ocultar mensaje después de 5 segundos
setTimeout(function() {
    const mensaje = document.getElementById('flash-message');
    if (mensaje) {
        mensaje.classList.add('hide');
        setTimeout(() => mensaje.remove(), 500);
    }
}, 5000);

// Función para mostrar el modal de confirmación
function confirmarEliminacion(userId, usuario) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserUsuario').value = usuario;
    document.getElementById('usuarioEliminar').textContent = usuario;
    document.getElementById('confirmModal').style.display = 'block';
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera del contenido
window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target == modal) {
        cerrarModal();
    }
}

// Funciones para la eliminación masiva
function showDeleteAllConfirmation() {
    document.getElementById('deleteAllModal').style.display = 'block';
}

function closeDeleteAllModal() {
    document.getElementById('deleteAllModal').style.display = 'none';
}

function showFinalConfirmation() {
    closeDeleteAllModal();
    document.getElementById('doubleConfirm').style.display = 'block';
    document.getElementById('doubleConfirm').scrollIntoView({ behavior: 'smooth' });
}

function cancelDeleteAll() {
    document.getElementById('doubleConfirm').style.display = 'none';
}

// Validar el texto de confirmación
document.getElementById('confirmText').addEventListener('input', function() {
    const confirmBtn = document.getElementById('finalConfirmBtn');
    confirmBtn.disabled = this.value !== 'ELIMINAR TODOS LOS EMPLEADOS';
});

// Confirmación adicional antes de enviar
document.getElementById('deleteAllForm').addEventListener('submit', function(e) {
    if (!confirm('¿ESTÁS ABSOLUTAMENTE SEGURO?\n\nESTA ACCIÓN ELIMINARÁ PERMANENTEMENTE TODOS LOS EMPLEADOS.')) {
        e.preventDefault();
    }
});

// ===================== fin admin.php ===================== \\