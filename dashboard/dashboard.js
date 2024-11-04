document.addEventListener('DOMContentLoaded', function () {
    const configIcon = document.getElementById('config-icon');
    const modal = document.getElementById('config-modal');
    const closeModal = document.getElementById('close-modal');
    const mainContent = document.getElementById('main-content');

    configIcon.addEventListener('click', function () {
        modal.style.display = 'block';
        mainContent.classList.add('blur');  // Difuminar el contenido
    });

    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
        mainContent.classList.remove('blur');  // Quitar el efecto difuminado
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            mainContent.classList.remove('blur');  // Quitar el efecto difuminado
        }
    });
});

// PRESUPUESTO
// Abrir modal de presupuesto
document.querySelectorAll('.btn-presupuesto').forEach(button => {
    button.onclick = function() {
        const monto = this.getAttribute('data-monto');
        const estado = this.getAttribute('data-estado');
        const comentario = this.getAttribute('data-comentario'); // Obtener comentario

        document.getElementById('presupuestoMonto').textContent = monto;
        document.getElementById('presupuestoEstado').textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
        document.getElementById('presupuestoComentario').textContent = comentario || "Sin comentario"; // Mostrar comentario o mensaje por defecto

        // Mostrar u ocultar botones dependiendo del estado del presupuesto
        const presupuestoAcciones = document.getElementById('presupuestoAcciones');
        if (estado === 'pendiente') {
            presupuestoAcciones.style.display = 'block';
            document.getElementById('btnAceptarPresupuesto').onclick = function() {
                gestionarPresupuesto(button.getAttribute('data-idform'), 'aceptado');
            };
            document.getElementById('btnRechazarPresupuesto').onclick = function() {
                gestionarPresupuesto(button.getAttribute('data-idform'), 'rechazado');
            };
        } else {
            presupuestoAcciones.style.display = 'none';
        }

        // Mostrar el modal de presupuesto
        document.getElementById('modalPresupuesto').style.display = 'block';
    };
});

// Cerrar modal de presupuesto
document.querySelector(".cerrar-presupuesto").onclick = function() {
    document.getElementById("modalPresupuesto").style.display = "none";
};


// Función para gestionar aceptación o rechazo del presupuesto
function gestionarPresupuesto(idForm, estado) {
    const mensajeReagendar = document.getElementById('mensaje-error'); // Contenedor de mensajes
    mensajeReagendar.textContent = "Procesando, por favor espere...";
    mensajeReagendar.classList.remove("exito", "error", "procesando");
    mensajeReagendar.classList.add("procesando");

    $.ajax({
        url: 'gestionar_presupuesto.php',
        method: 'POST',
        data: { id_form: idForm, estado: estado },
        success: function(response) {
            const result = JSON.parse(response);

            if (result.success) {
                mensajeReagendar.textContent = result.message;
                mensajeReagendar.classList.remove("procesando");
                mensajeReagendar.classList.add("exito");

                setTimeout(() => {
                    window.location.reload();
                }, 2000); // Recargar después de 2 segundos
            } else {
                mensajeReagendar.textContent = result.message;
                mensajeReagendar.classList.remove("procesando");
                mensajeReagendar.classList.add("error");
            }
        },
        error: function() {
            mensajeReagendar.textContent = "Error al gestionar el presupuesto.";
            mensajeReagendar.classList.remove("procesando");
            mensajeReagendar.classList.add("error");
        }
    });
}



window.onclick = function(event) {
    const modalPresupuesto = document.getElementById("modalPresupuesto");
    if (event.target == modalPresupuesto) {
        modalPresupuesto.style.display = "none";
    }
};

// Obtener elementos de la modal
var modalCancelar = document.getElementById("modalCancelar");
var closeCancelar = document.getElementById("closeCancelar");
var confirmarCancelar = document.getElementById("confirmarCancelar");
var cancelarCancelar = document.getElementById("cancelarCancelar");

// Función para abrir la modal de confirmación de cancelación
function abrirModalCancelar(idForm) {
    modalCancelar.style.display = "block";
    
    confirmarCancelar.onclick = function() {
        window.location.href = `cancelar.php?id_form=${idForm}`;
    };
}

// Cerrar modal al hacer clic en la 'X' o en 'Cancelar'
closeCancelar.onclick = function() {
    modalCancelar.style.display = "none";
};

cancelarCancelar.onclick = function() {
    modalCancelar.style.display = "none";
};

// Cerrar modal si el usuario hace clic fuera de ella
window.onclick = function(event) {
    if (event.target == modalCancelar) {
        modalCancelar.style.display = "none";
    }
};


//REAGENDAR
// Script para abrir el modal de reagendar
var modalReagendar = document.getElementById("modalReagendar");
var closeReagendar = document.getElementById("closeReagendar");

function abrirModalReagendar(id_form) {
    document.getElementById('id_form').value = id_form;
    modalReagendar.style.display = "block";
}

closeReagendar.onclick = function() {
    modalReagendar.style.display = "none";
}

// Cargar horarios disponibles al seleccionar la fecha
$(document).ready(function() {
    $('#fecha').change(function() {
        var fechaSeleccionada = $(this).val();
        $.ajax({
            url: '../formulario/obtener_horarios.php',
            method: 'GET',
            data: { date: fechaSeleccionada },
            success: function(data) {
                var horarios = JSON.parse(data);
                $('#horario').html(''); // Limpiar opciones anteriores
                horarios.forEach(function(horario) {
                    $('#horario').append('<option value="' + horario.id_horario + '">' + horario.hora_disponible + '</option>');
                });
            }
        });
    });
});

// Enviar los datos del modal usando AJAX
document.getElementById("formReagendar").onsubmit = function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    var mensajeReagendar = document.getElementById('mensaje-reagendar');

    // Mostrar el mensaje de "Por favor, espere"
    mensajeReagendar.textContent = "Por favor, espere y no cierre la ventana...";
    mensajeReagendar.classList.remove('exito', 'error'); // Remover estilos anteriores
    mensajeReagendar.classList.add('procesando'); // Mostrar mensaje en color naranja

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "reagendar.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                mensajeReagendar.textContent = "¡Su cita fue reagendada exitosamente!";
                mensajeReagendar.classList.remove('procesando');
                mensajeReagendar.classList.add('exito'); // Cambiar a mensaje verde

                // Actualizar la tabla o cerrar el modal después de un tiempo
                setTimeout(function() {
                    window.location.reload(); // Recargar la página después de 2 segundos
                }, 2000);
            } else {
                mensajeReagendar.textContent = "Error: " + response.message;
                mensajeReagendar.classList.remove('procesando');
                mensajeReagendar.classList.add('error'); // Mostrar mensaje de error en rojo
            }
        } else {
            mensajeReagendar.textContent = "Error en la solicitud.";
            mensajeReagendar.classList.remove('procesando');
            mensajeReagendar.classList.add('error'); // Mostrar mensaje de error en rojo
        }
    };
    xhr.send(formData);
};


//DETALLES
// Script para el modal de detalles (igual al anterior)
var modalDetalles = document.getElementById("modalDetalles");
var spanDetalles = document.getElementsByClassName("cerrar-detalles")[0];

spanDetalles.onclick = function() {
    modalDetalles.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modalDetalles) {
        modalDetalles.style.display = "none";
    }
}

function mostrarDetalles(detallesCita) {
    var contenidoDetalles = document.getElementById("contenidoDetalles");
    contenidoDetalles.innerHTML = `
        <p><strong>Número de Cita:</strong> ${detallesCita.id_form}</p>
        <p><strong>Nombre:</strong> ${detallesCita.nombre} ${detallesCita.apellido}</p>
        <p><strong>RUT:</strong> ${detallesCita.rut}</p>
        <p><strong>Correo:</strong> ${detallesCita.correo}</p>
        <p><strong>Dirección:</strong> ${detallesCita.direccion}</p>
        <p><strong>Teléfono:</strong> ${detallesCita.telefono}</p>
        <p><strong>Detalles:</strong> ${detallesCita.detalles}</p>
        <p><strong>Servicio:</strong> ${detallesCita.tipo_servicio}</p>
        <p><strong>Producto:</strong> ${detallesCita.tipo_producto}</p>
        <p><strong>Fecha:</strong> ${detallesCita.fecha}</p>
        <p><strong>Hora:</strong> ${detallesCita.hora}</p>
    `;
    modalDetalles.style.display = "block";
}

//CONFIGURACIÓN
document.addEventListener('DOMContentLoaded', function () {
    const formConfiguracion = document.getElementById('formConfiguracion');
    const mensajeConfiguracion = document.getElementById('mensaje-configuracion'); // Contenedor de mensajes
    const nuevoCorreoInput = document.getElementById('nuevo_correo');
    const confirmarCorreoInput = document.getElementById('confirmar_correo');

    formConfiguracion.onsubmit = function (e) {
        e.preventDefault();

        // Limpiar mensajes antes de validar
        mensajeConfiguracion.textContent = "";
        mensajeConfiguracion.classList.remove("exito", "error", "procesando");

        // Validar que los correos coincidan
        if (nuevoCorreoInput.value && nuevoCorreoInput.value !== confirmarCorreoInput.value) {
            mensajeConfiguracion.textContent = "Los correos electrónicos no coinciden.";
            mensajeConfiguracion.classList.add("error");
            return;
        }

        const formData = new FormData(formConfiguracion);

        // Mostrar mensaje de "procesando"
        mensajeConfiguracion.textContent = "Procesando, por favor espere...";
        mensajeConfiguracion.classList.add("procesando");

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "procesar_configuracion.php", true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    mensajeConfiguracion.textContent = response.message;
                    mensajeConfiguracion.classList.remove("procesando");
                    mensajeConfiguracion.classList.add("exito");

                    // Recargar la página después de 5 segundos
                    setTimeout(function () {
                        window.location.reload();
                    }, 5000);
                } else {
                    mensajeConfiguracion.textContent = response.message;
                    mensajeConfiguracion.classList.remove("procesando");
                    mensajeConfiguracion.classList.add("error");
                }
            } else {
                mensajeConfiguracion.textContent = "Error en la solicitud. Inténtelo de nuevo.";
                mensajeConfiguracion.classList.remove("procesando");
                mensajeConfiguracion.classList.add("error");
            }
        };
        xhr.send(formData);
    };
});

//MENSAJES
let intervaloMensajes;  // Variable para controlar el intervalo

// Función para cargar los mensajes periódicamente
function cargarMensajesPeriodicamente(idForm) {
    // Limpiar cualquier intervalo anterior
    clearInterval(intervaloMensajes);

    // Definir el intervalo para hacer la solicitud cada 3 segundos
    intervaloMensajes = setInterval(() => {
        fetch(`obtener_mensajes.php?id_form=${idForm}`)
        .then(response => response.json())
        .then(data => {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';  // Limpiar los mensajes anteriores
            data.mensajes.forEach(mensaje => {
                const divMensaje = document.createElement('div');
                divMensaje.classList.add(mensaje.esAdmin ? 'mensaje-admin' : 'mensaje-cliente');
                divMensaje.innerHTML = `<strong>${mensaje.nombre}:</strong> ${mensaje.mensaje}`;
                chatMessages.appendChild(divMensaje);
            });

            
        });
    }, 1000);  // Realizar la solicitud cada 1 segundos
}

// Función para verificar mensajes no leídos periódicamente
function verificarMensajesNoLeidosPeriodicamente() {
    setInterval(() => {
        fetch('verificar_mensajes_no_leidos.php')  // Reutilizamos el mismo archivo
        .then(response => response.json())
        .then(data => {
            data.formularios.forEach(formulario => {
                const botonChat = document.querySelector(`.btn-chat[data-idform="${formulario.id_form}"]`);
                if (botonChat) {
                    // Si hay mensajes no leídos, cambiar el color a rojo, si no, cambiar a azul
                    botonChat.style.backgroundColor = formulario.mensajes_no_leidos > 0 ? '#FF0000' : '#02B1F4';
                }
            });
        })
        .catch(error => console.error('Error verificando mensajes no leídos:', error));
    }, 2000); // Verificar cada 5 segundos
}

const botonesChat = document.querySelectorAll('.btn-chat');
botonesChat.forEach((boton) => {
    boton.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        const estadoCita = this.getAttribute('data-estado');  // Obtenemos el estado de la cita
        document.getElementById('id_form_chat').value = idForm;
        document.getElementById('chatIdForm').innerText = idForm;
        document.getElementById('modalChat').style.display = 'block';
        
        // Si la cita está cancelada o finalizada, deshabilitamos el campo de texto y el botón
        const mensajeTextarea = document.getElementById('mensaje_chat');
        const botonEnviar = document.querySelector('#formEnviarMensaje button');
        if (estadoCita === 'cancelado' || estadoCita === 'finalizado') {
            mensajeTextarea.disabled = true;
            mensajeTextarea.placeholder = 'Ya no se pueden enviar mensajes.';  // Muestra este mensaje en el campo de texto
            botonEnviar.disabled = true;
        } else {
            mensajeTextarea.disabled = false;
            mensajeTextarea.placeholder = 'Escribe tu mensaje...';  // Mensaje normal cuando se pueden enviar mensajes
            botonEnviar.disabled = false;
        }

        // Marcar los mensajes del administrador como leídos al abrir el chat
        fetch(`marcar_mensajes_como_leidos.php?id_form=${idForm}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cargar los mensajes periódicamente después de marcar como leídos
                cargarMensajesPeriodicamente(idForm);
            }
        });
    };
});



// Detener el polling cuando se cierra el modal
const cerrarModalChat = document.querySelector('.cerrar-chat');
if (cerrarModalChat) {
    cerrarModalChat.onclick = function() {
        document.getElementById('modalChat').style.display = 'none';
        clearInterval(intervaloMensajes);  // Detener el intervalo al cerrar el modal
    };
}


// Función para enviar un nuevo mensaje
const formEnviarMensaje = document.getElementById('formEnviarMensaje');
if (formEnviarMensaje) {
    formEnviarMensaje.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const mensajeDiv = document.getElementById('mensaje-chat');

        fetch('./enviar_mensaje.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mensajeDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                // Cargar los mensajes inmediatamente después de enviar uno
                cargarMensajesPeriodicamente(document.getElementById('id_form_chat').value);
                document.getElementById('mensaje_chat').value = '';  // Limpiar el campo de texto
            } else {
                mensajeDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
            }
        });
    };
}




// Función para verificar mensajes no leídos al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    verificarMensajesNoLeidosPeriodicamente();  // Verificar mensajes no leídos cada 5 segundos
});

