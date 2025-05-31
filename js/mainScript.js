// Cerrar sesion
function cerrarSesion() {
    // Redirigir al archivo cerrarSesion.php
    window.location.href = 'php/cerrarSesion.php';
}

//Funcion para controlar el sidebar derecho
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const isActive = sidebar.classList.contains('active');

    if (isActive) {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
    } else {
        sidebar.classList.add('active');
        overlay.style.display = 'block';
    }
}

// Abrir la ventana de confirmación
function showLogoutConfirmation() {
    document.getElementById('confirm-logout-overlay').style.display = 'block';
    document.getElementById('confirm-logout').style.display = 'block';
}

// Cerrar la ventana de confirmación
function closeLogoutConfirmation() {
    document.getElementById('confirm-logout-overlay').style.display = 'none';
    document.getElementById('confirm-logout').style.display = 'none';
}

// Función para controlar el sidebar izquierdo
function toggleMenuSidebar() {
    const menuSidebar = document.getElementById('menu-sidebar');
    const menuOverlay = document.getElementById('menu-overlay');
    const isActive = menuSidebar.classList.contains('active');

    if (isActive) {
        menuSidebar.classList.remove('active');
        menuOverlay.style.display = 'none';
    } else {
        menuSidebar.classList.add('active');
        menuOverlay.style.display = 'block';
    }
}

function openEmployeeDetails(button) {
    const employeeClave = button.getAttribute('data-clave'); // Obtener la clave del empleado

    // Realizar la solicitud AJAX
    fetch('./php/get_employee.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `clave=${employeeClave}` // Enviar la clave en lugar del ID
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }

        // Llenar los detalles del empleado
        document.querySelector('.details-top img').src = data.img || (data.sexo === 'Hombre' ? 'https://avatar.iran.liara.run/public/7' : 'https://avatar.iran.liara.run/public/55');
        document.querySelector('.details-top h1').textContent = data.nombre;
        document.querySelector('.details-top p:nth-child(2)').textContent = data.clave;
        document.querySelector('.details-top p:nth-child(3)').textContent = data.funcion_empleado;
        document.querySelector('.details-top p:nth-child(4)').textContent = data.puesto;
        document.querySelector('.details-top p:nth-child(5)').textContent = data.tipo_empleado;
        document.querySelector('.details-top p:nth-child(6)').textContent = data.email_tecnm;

        const detailsMiddle = document.querySelectorAll('.details-middle p span');
        detailsMiddle[0].textContent = data.email_personal;
        detailsMiddle[1].textContent = data.telefono;
        detailsMiddle[2].textContent = new Date(data.fecha_nacimiento).toLocaleDateString();
        detailsMiddle[3].textContent = new Date(data.fecha_ingreso).toLocaleDateString();
        detailsMiddle[4].textContent = data.fecha_baja ? new Date(data.fecha_baja).toLocaleDateString() : 'NA';
        detailsMiddle[5].textContent = data.afiliacion;

        const detailsBottom = document.querySelectorAll('.details-bottom p span');
        detailsBottom[0].textContent = data.tipo_sangre;
        detailsBottom[1].textContent = data.sexo;
        detailsBottom[2].textContent = data.estado_civil;
        detailsBottom[3].textContent = data.curp;
        detailsBottom[4].textContent = data.puesto;
        detailsBottom[5].textContent = data.domicilio;
        detailsBottom[6].textContent = data.escolaridad;
        detailsBottom[7].textContent = data.rfc;

        // Mostrar el cuadro de detalles
        document.getElementById('employee-details').style.display = 'block';
        document.getElementById('employee-details-overlay').style.display = 'block';
    })
    .catch(error => console.error('Error al obtener los datos del empleado:', error));
}



// Función para guardar la clave del empleado en PHP
function saveEmployeeKeyToSession(clave) {
    fetch('./php/saveEmployeeKey.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ clave: clave }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Clave guardada en la sesión:', clave);
        } else {
            console.error('Error al guardar la clave en la sesión:', data.error);
        }
    })
    .catch(error => console.error('Error al guardar la clave en la sesión:', error));
}



// Función para cerrar el cuadro de detalles del empleado
function closeEmployeeDetails() {
    document.getElementById('employee-details-overlay').style.display = 'none';
    document.getElementById('employee-details').style.display = 'none';
}


// Redirige a la página form_empleados.php
function redirectToFormEmpleado() {
    window.location.href = '../public/form_empleados.php';
}

let currentLetterFilter = "All"; // Filtro actual para las letras
let currentCategoryFilter = "Todos"; // Filtro actual para las categorías

// Función para actualizar las tarjetas basándose en ambos filtros
function applyFilters() {
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        const cardName = card.querySelector('.card-name').textContent.trim();
        const cardRole = card.querySelector('.card-role').textContent.trim();

        const matchesLetter = (currentLetterFilter === "All") || (cardName.startsWith(currentLetterFilter));
        const matchesCategory = (currentCategoryFilter === "Todos") || (cardRole === currentCategoryFilter);

        // Mostrar la tarjeta si cumple ambas condiciones
        if (matchesLetter && matchesCategory) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

// Evento para filtrar por letra
document.querySelectorAll('.letter').forEach(button => {
    button.addEventListener('click', () => {
        currentLetterFilter = button.textContent.trim(); // Actualizar el filtro de letras
        applyFilters(); // Aplicar los filtros
        document.querySelectorAll('.letter').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
    });
});

// Evento para filtrar por categoría
document.querySelectorAll('.category').forEach(button => {
    button.addEventListener('click', () => {
        currentCategoryFilter = button.textContent.trim(); // Actualizar el filtro de categorías
        applyFilters(); // Aplicar los filtros
        document.querySelectorAll('.category').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
    });
});

// Función para desplazar hacia arriba
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth' // Desplazamiento suave
    });
}

// Mostrar u ocultar el botón dependiendo del scroll
window.addEventListener('scroll', () => {
    const scrollToTopButton = document.querySelector('.scroll-to-top');
    if (window.scrollY > 300) {
        scrollToTopButton.style.display = 'block'; // Mostrar el botón
    } else {
        scrollToTopButton.style.display = 'none'; // Ocultar el botón
    }
});

// Añadir el evento click al botón
document.querySelector('.scroll-to-top').addEventListener('click', scrollToTop);

function showDeleteConfirmation(id) {
    // Muestra el overlay y la ventana de confirmación
    document.getElementById('delete-confirm-overlay').style.display = 'block';
    document.getElementById('delete-confirm').style.display = 'block';

    // Establece el ID del empleado en el campo oculto del formulario
    document.getElementById('delete-id').value = id;
}

function closeDeleteConfirmation() {
    // Oculta el overlay y la ventana de confirmación
    document.getElementById('delete-confirm-overlay').style.display = 'none';
    document.getElementById('delete-confirm').style.display = 'none';
}

// Filtrar tarjetas según la búsqueda
document.querySelector('.search-bar input').addEventListener('input', function () {
    const searchText = this.value.toLowerCase();
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        const name = card.querySelector('.card-name').textContent.toLowerCase();
        if (name.includes(searchText)) {
            card.style.display = 'block'; // Mostrar tarjeta si coincide
        } else {
            card.style.display = 'none'; // Ocultar tarjeta si no coincide
        }
    });
});

document.getElementById('btnImportarExcel').addEventListener('click', function () {
  document.getElementById('inputExcel').click();
});

document.getElementById('inputExcel').addEventListener('change', function (event) {
  const archivo = event.target.files[0];
  if (!archivo) return;

  const formData = new FormData();
  formData.append('archivo_excel', archivo);

  fetch('importar_excel.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.text())
    .then(html => {
      document.getElementById('contenidoResultado').innerHTML = html;
      document.getElementById('resultadoImportacion').style.display = 'block';
    })
    .catch(error => {
      document.getElementById('contenidoResultado').innerHTML = `<p>Error al subir el archivo: ${error}</p>`;
      document.getElementById('resultadoImportacion').style.display = 'block';
    });
});

document.getElementById('cerrarResultado').addEventListener('click', function () {
  document.getElementById('resultadoImportacion').style.display = 'none';
});

