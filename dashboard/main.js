// add hovered class to selected list item
let list = document.querySelectorAll(".navigation li");

function activeLink() {
    list.forEach((item) => {
        item.classList.remove("hovered");
    });
    this.classList.add("hovered");
}

list.forEach((item) => item.addEventListener("mouseover", activeLink));

// Menu Toggle
let toggle = document.querySelector(".toggle");
let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");

// Función para alternar el menú
toggle.onclick = function () {
    navigation.classList.toggle("active");
    main.classList.toggle("active");
};

// Contraer el menú cuando se hace clic en una opción (en cualquier tamaño de pantalla)
let navLinks = document.querySelectorAll(".navigation ul li a");
navLinks.forEach(link => {
    link.addEventListener("click", function () {
        navigation.classList.add("active");
        main.classList.add("active");
    });
});



function loadContent(url) {
    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('content').innerHTML = data;
            // Reasignar eventos después de cargar el contenido dinámico
            rebindModalFunctions();  
            bindAddHourButton();  // Reasignar evento para botón de agregar hora
        })
        .catch(error => console.error('Error al cargar el contenido:', error));
}

// Función para manejar el botón de "Agregar otra hora"
function bindAddHourButton() {
    const agregarHoraBtn = document.getElementById('agregar-hora');
    if (agregarHoraBtn) {
        agregarHoraBtn.addEventListener('click', function () {
            const container = document.getElementById('horas-container');
            const input = document.createElement('input');
            input.type = 'time';
            input.name = 'horas[]';
            input.required = true;
            container.appendChild(input);
        });
    }
}

// Función para cargar las citas de un cliente
function verCitas(rut) {
    loadContent('obtener_citas.php?rut=' + rut);  // Cargar citas del cliente usando su RUT
}

// Función para cargar todas las citas
function verTodasCitas() {
    loadContent('ver_citas.php');  // Cargar todas las citas
}

// Función para actualizar los datos de las tarjetas
function actualizarDatosDashboard() {
    fetch('recoleccion_datos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar la tarjeta de Total de Citas
                document.getElementById('total-citas-en-proceso').textContent = data.total_citas_en_proceso;
                document.getElementById('total-citas-en-proceso-nombre').textContent = "Total de citas en proceso";

                // Actualizar la tarjeta de Total de Clientes
                document.getElementById('total-clientes').textContent = data.total_clientes;
                document.getElementById('total-clientes-nombre').textContent = "Total de Clientes";

                // Actualizar la tarjeta de Citas Pendientes
                document.getElementById('total-citas-pendientes').textContent = data.total_citas_pendientes;
                document.getElementById('total-citas-pendientes-nombre').textContent = "Citas Pendientes";
                
                // Actualizar la tarjeta de Chats Pendientes
                document.getElementById('total-chats-pendientes').textContent = data.total_chats_pendientes;
                document.getElementById('total-chats-pendientes-nombre').textContent = "Chats Pendientes";
            } else {
                console.error('Error al obtener datos: ' + data.message);
            }
        })
        .catch(error => console.error('Error al cargar los datos del dashboard:', error));
}

// Ejecutar la función para actualizar los datos del dashboard cuando la página esté cargada
document.addEventListener('DOMContentLoaded', function () {
    // Llamar la función de inmediato al cargar la página
    actualizarDatosDashboard();

    // Establecer un intervalo para actualizar los datos cada 5 segundos (5000 ms)
    setInterval(actualizarDatosDashboard, 3000); // Puedes ajustar el intervalo según lo necesites
});





// Función para cargar y mostrar las últimas citas y clientes (contenido inicial del Dashboard)
function cargarUltimosDatos() {
    fetch('recoleccion_datos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar los datos de citas
                const tablaCitas = document.getElementById('tabla-ultimas-citas');
                tablaCitas.innerHTML = ''; // Limpiar el contenido anterior
                data.ultimas_citas.forEach(cita => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${cita.id_form}</td>
                        <td>${cita.nombre}</td>
                        <td>${cita.apellido}</td>
                        <td>${cita.correo}</td>
                        <td>${cita.telefono}</td>
                        <td>${cita.estado}</td>
                    `;
                    tablaCitas.appendChild(row);
                });

                // Actualizar los datos de clientes
                const tablaClientes = document.getElementById('tabla-ultimos-clientes');
                tablaClientes.innerHTML = ''; // Limpiar el contenido anterior
                data.ultimos_clientes.forEach(cliente => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${cliente.id}</td>
                        <td>${cliente.rut}</td>
                        <td>${cliente.nombre}</td>
                        <td>${cliente.email}</td>
                        <td>${cliente.fecha_registro}</td>
                    `;
                    tablaClientes.appendChild(row);
                });

                // Reiniciar el contenido inicial
                document.getElementById('content').innerHTML = document.getElementById('content').innerHTML;
            }
        })
        .catch(error => console.error('Error al cargar los datos:', error));
}



// Ejecutar la función cuando la página esté cargada para mostrar el contenido inicial
document.addEventListener('DOMContentLoaded', function () {
    cargarUltimosDatos(); // Cargar citas y clientes al iniciar
});



// Obtener los elementos clave del DOM
const iconoNotificaciones = document.getElementById('icono-notificaciones');
const dropdownNotificaciones = document.getElementById('notificaciones-dropdown');
const puntoNotificacion = document.getElementById('punto-notificacion');  // Asegúrate de tener este elemento en tu HTML

// Obtener el último ID de cita visto desde localStorage (si existe)
let ultimoIdCitaVisto = localStorage.getItem('ultimoIdCitaVisto') || null;

// Función para mostrar/ocultar el punto de notificación
function mostrarPuntoNotificacion(hayNuevasCitas) {
    if (hayNuevasCitas) {
        puntoNotificacion.classList.add('visible'); // Mostrar el punto de notificación si hay nuevas citas
    } else {
        puntoNotificacion.classList.remove('visible'); // Ocultar el punto si no hay nuevas citas
    }
}

// Función para cargar y actualizar las notificaciones
function cargarNotificaciones() {
    fetch('obtener_notificaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const listaNotificaciones = document.getElementById('lista-notificaciones');
                listaNotificaciones.innerHTML = ''; // Limpiar notificaciones anteriores

                if (data.notificaciones.length > 0) {
                    // Obtener el ID de la cita más reciente
                    const idCitaMasReciente = parseInt(data.notificaciones[0].match(/#(\d+)/)[1]);

                    // Verificar si la cita más reciente es una nueva que no ha sido vista
                    if (!ultimoIdCitaVisto || idCitaMasReciente > parseInt(ultimoIdCitaVisto)) {
                        mostrarPuntoNotificacion(true); // Mostrar el punto si hay nuevas citas
                    } else {
                        mostrarPuntoNotificacion(false); // No hay nuevas citas
                    }

                    // Agregar las nuevas notificaciones a la lista
                    data.notificaciones.forEach(notificacion => {
                        const li = document.createElement('li');
                        li.textContent = notificacion;
                        listaNotificaciones.appendChild(li);
                    });
                } else {
                    mostrarPuntoNotificacion(false); // No hay notificaciones, ocultar el punto
                }
            }
        })
        .catch(error => console.error('Error al cargar notificaciones:', error));
}

// Evento para ocultar el punto de notificación cuando el icono es clicado
iconoNotificaciones.addEventListener('click', function() {
    // Marcar las citas como vistas y almacenar el ID de la última cita
    const ultimaCita = document.querySelector('#lista-notificaciones li:first-child');
    if (ultimaCita) {
        const idCita = ultimaCita.textContent.match(/#(\d+)/)[1];
        localStorage.setItem('ultimoIdCitaVisto', idCita); // Almacenar el último ID de cita visto
        ultimoIdCitaVisto = idCita; // Actualizar el valor en la variable
    }

    mostrarPuntoNotificacion(false); // Marcar las citas como vistas
    dropdownNotificaciones.classList.toggle('visible'); // Mostrar/Ocultar el dropdown
});

// Configurar el intervalo para verificar nuevas notificaciones cada 5 segundos
setInterval(cargarNotificaciones, 5000);

// Ejecutar la función cuando la página esté cargada
document.addEventListener('DOMContentLoaded', function () {
    cargarNotificaciones(); // Cargar notificaciones al iniciar
});



function verificarMensajesNoLeidosPeriodicamente() {
    setInterval(() => {
        // Hacer la solicitud para verificar si hay mensajes no leídos
        fetch('verificar_mensajes_no_leidos.php')
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
    }, 2000); // Verificar cada 5 segundos (puedes ajustar el tiempo según sea necesario)
}

// Llamar a la función al cargar la página para verificar mensajes no leídos en tiempo real
document.addEventListener('DOMContentLoaded', function () {
    verificarMensajesNoLeidosPeriodicamente();  // Verificar mensajes no leídos cada 5 segundos
});



function rebindModalFunctions() {
    
    let intervaloMensajes;  // Variable para almacenar el intervalo de actualización de mensajes

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
                let hayMensajesNoLeidos = false;
    
                data.mensajes.forEach(mensaje => {
                    const divMensaje = document.createElement('div');
                    divMensaje.classList.add(mensaje.esAdmin ? 'mensaje-admin' : 'mensaje-cliente');
                    divMensaje.innerHTML = `<strong>${mensaje.nombre}:</strong> ${mensaje.mensaje}`;
                    chatMessages.appendChild(divMensaje);
    
                    // Verificar si hay mensajes no leídos del cliente
                    if (!mensaje.esAdmin && !mensaje.leido) {
                        hayMensajesNoLeidos = true;
                    }
                });
    
                // Hacer scroll automáticamente hasta el final del chat
                chatMessages.scrollTop = chatMessages.scrollHeight;
    
                // Actualizar el color del botón de chat si hay mensajes no leídos
                const botonChat = document.querySelector(`.btn-chat[data-idform="${idForm}"]`);
                if (botonChat) {
                    botonChat.style.backgroundColor = hayMensajesNoLeidos ? '#FF0000' : '#02B1F4';
                }
            });
        }, 1000);  // Realizar la solicitud cada 3 segundos
    }
    
// Función para abrir el chat y cargar los mensajes
const botonesChat = document.querySelectorAll('.btn-chat');
botonesChat.forEach((boton) => {
    boton.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        const estadoCita = this.getAttribute('data-estado'); // Obtener el estado de la cita

        document.getElementById('id_form_chat').value = idForm;
        document.getElementById('chatIdForm').innerText = idForm;
        document.getElementById('modalChat').style.display = 'block';

        const mensajeInput = document.getElementById('mensaje_chat');
        const botonEnviarMensaje = document.querySelector('.btn-enviar-mensaje');

        // Verificar si la cita está cancelada o finalizada
        if (estadoCita === 'cancelado' || estadoCita === 'finalizado') {
            mensajeInput.disabled = true;  // Deshabilitar el campo de texto
            botonEnviarMensaje.disabled = true;  // Deshabilitar el botón de enviar
            mensajeInput.placeholder = "No se pueden enviar mensajes para citas finalizadas o canceladas"; // Cambiar el placeholder
        } else {
            mensajeInput.disabled = false;  // Habilitar el campo de texto
            botonEnviarMensaje.disabled = false;  // Habilitar el botón de enviar
            mensajeInput.placeholder = "Escribe tu mensaje..."; // Restaurar el placeholder
        }

        // Marcar los mensajes como leídos al abrir el chat
        fetch(`marcar_mensajes_como_leidos.php?id_form=${idForm}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cargar los mensajes periódicamente
                cargarMensajesPeriodicamente(idForm);
            }
        });
    };
});


    // Función para cerrar el modal del chat
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

            fetch('enviar_mensaje.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mensajeDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                    // Recargar los mensajes inmediatamente después de enviar uno
                    cargarMensajesPeriodicamente(document.getElementById('id_form_chat').value);
                    // Limpiar el campo de texto del mensaje
                    document.getElementById('mensaje_chat').value = '';
                } else {
                    mensajeDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
                }
            });
        };
    }

    

    const botonesDetalles = document.querySelectorAll('.btn-detalles');
    botonesDetalles.forEach((boton) => {
        boton.onclick = function() {
            const detalles = JSON.parse(this.getAttribute('data-detalles'));
            mostrarDetalles(detalles);
        };
    });

    // Mostrar modal de presupuesto
    const botonesPresupuesto = document.querySelectorAll('.btn-presupuesto');
    botonesPresupuesto.forEach((boton) => {
        boton.onclick = function() {
            const idForm = this.getAttribute('data-idform');
            const formPresupuesto = document.getElementById('id_form_presupuesto');
            if (formPresupuesto) {
                formPresupuesto.value = idForm;
                document.getElementById('modalPresupuesto').style.display = 'block';
            }
        };
    });

    // Mostrar modal de ver presupuesto pendiente
const botonesVerPresupuestoPendiente = document.querySelectorAll('.btn-ver-presupuesto-pendiente');
botonesVerPresupuestoPendiente.forEach((boton) => {
    boton.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        const monto = this.getAttribute('data-monto');
        const comentario = this.getAttribute('data-comentario');
        
        // Llenar el contenido del modal con los detalles del presupuesto pendiente
        const modalPresupuesto = document.getElementById('modalPresupuestoPendiente');
        const presupuestoDetalles = document.getElementById('presupuestoDetallesPendiente');
        presupuestoDetalles.innerHTML = `
            <p><strong>ID Formulario:</strong> ${idForm}</p>
            <p><strong>Monto:</strong> ${monto}</p>
            <p><strong>Comentario:</strong> ${comentario}</p>
        `;
        
        // Mostrar el modal de presupuesto pendiente
        modalPresupuesto.style.display = 'block';
    };
});


    // Mostrar modal de ver presupuesto aceptado
const botonesVerPresupuesto = document.querySelectorAll('.btn-ver-presupuesto');
botonesVerPresupuesto.forEach((boton) => {
    boton.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        const monto = this.getAttribute('data-monto');
        const comentario = this.getAttribute('data-comentario');
        
        // Llenar el contenido del modal con los detalles del presupuesto
        const modalPresupuesto = document.getElementById('modalPresupuestoAceptado');
        const presupuestoDetalles = document.getElementById('presupuestoDetalles');
        presupuestoDetalles.innerHTML = `
            <p><strong>ID Formulario:</strong> ${idForm}</p>
            <p><strong>Monto:</strong> ${monto}</p>
            <p><strong>Comentario:</strong> ${comentario}</p>
        `;
        
        // Mostrar el modal de presupuesto aceptado
        modalPresupuesto.style.display = 'block';
    };
});

    // Verificar que el formulario de presupuesto existe antes de asignar el evento
    const formPresupuesto = document.getElementById("formPresupuesto");
    if (formPresupuesto) {
        formPresupuesto.onsubmit = function(e) {
            e.preventDefault(); // Evitar el envío tradicional del formulario

            const formData = new FormData(this); // Obtener los datos del formulario
            const mensajePresupuesto = document.getElementById('mensaje-presupuesto'); // Div donde se mostrará el mensaje

            // Mostrar mensaje de "procesando"
            mensajePresupuesto.textContent = "Procesando, por favor espere...";
            mensajePresupuesto.classList.remove("exito", "error"); // Limpiar cualquier clase previa
            mensajePresupuesto.classList.add("procesando"); // Clase para estado de procesamiento

            // Enviar datos usando Fetch
            fetch("presupuesto.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // Parsear respuesta JSON
            .then(data => {
                // Mostrar el mensaje basado en la respuesta
                mensajePresupuesto.textContent = data.message;
                mensajePresupuesto.classList.remove("procesando"); // Remover la clase de procesamiento

                if (data.success) {
                    mensajePresupuesto.classList.add("exito"); // Clase para éxito
                    mensajePresupuesto.classList.remove("error");

                    // Ocultar el modal y limpiar el mensaje si es exitoso
                    setTimeout(() => {
                        document.getElementById("modalPresupuesto").style.display = "none";
                        mensajePresupuesto.textContent = ""; // Limpiar el mensaje
                        window.location.reload(); // Recargar la página después de cerrar el modal
                    }, 2000); // Ocultar el modal después de 2 segundos
                } else {
                    mensajePresupuesto.classList.add("error"); // Clase para error
                    mensajePresupuesto.classList.remove("exito");
                }
            })
            .catch(error => {
                mensajePresupuesto.textContent = "Ocurrió un error al procesar la solicitud.";
                mensajePresupuesto.classList.remove("procesando");
                mensajePresupuesto.classList.add("error");
            });
        };
    }

    // Mostrar modal de reagendar
    const botonesReagendar = document.querySelectorAll('.btn-reagendar');
    botonesReagendar.forEach((boton) => {
        boton.onclick = function() {
            const idForm = this.getAttribute('data-idform');
            const formReagendar = document.getElementById('id_form_reagendar');
            if (formReagendar) {
                formReagendar.value = idForm;
                document.getElementById('modalReagendar').style.display = 'block';
            }
        };
    });

// Mostrar modal de cambiar estado
const botonesEstado = document.querySelectorAll('.btn-estado');
botonesEstado.forEach((boton) => {
    boton.onclick = function() {
        const idForm = this.getAttribute('data-idform');
        const estadoActual = this.getAttribute('data-estado');  // Obtener el estado actual
        const formEstado = document.getElementById('id_form_estado');
        const selectEstado = document.getElementById('nuevo_estado');
        const mensajeEstado = document.getElementById('mensaje-estado'); // Obtener el contenedor de mensaje

        if (formEstado) {
            formEstado.value = idForm;

            // Limpiar las opciones previas del select
            selectEstado.innerHTML = '';

            if (estadoActual === 'en proceso') {
                // Si la cita está "en proceso", solo permitir cambiar a "finalizado"
                const optionFinalizado = document.createElement('option');
                optionFinalizado.value = 'finalizado';
                optionFinalizado.text = 'Finalizado';
                selectEstado.appendChild(optionFinalizado);
            } else if (estadoActual === 'pendiente') {
                // Si la cita está "pendiente", solo permitir cambiar a "confirmado" o "cancelado"
                const optionConfirmado = document.createElement('option');
                optionConfirmado.value = 'confirmado';
                optionConfirmado.text = 'Confirmado';
                selectEstado.appendChild(optionConfirmado);

                const optionCancelado = document.createElement('option');
                optionCancelado.value = 'cancelado';
                optionCancelado.text = 'Cancelado';
                selectEstado.appendChild(optionCancelado);
            } else if (estadoActual === 'confirmado') {
                // Si la cita está "confirmado", solo permitir cambiar a "cancelado" o "en proceso"
                const optionEnProceso = document.createElement('option');
                optionEnProceso.value = 'en proceso';
                optionEnProceso.text = 'En proceso';
                selectEstado.appendChild(optionEnProceso);

                const optionCancelado = document.createElement('option');
                optionCancelado.value = 'cancelado';
                optionCancelado.text = 'Cancelado';
                selectEstado.appendChild(optionCancelado);
            } else {
                // Para cualquier otro estado, mostrar todas las opciones
                const opciones = [
                    { value: 'pendiente', text: 'Pendiente' },
                    { value: 'confirmado', text: 'Confirmado' },
                    { value: 'cancelado', text: 'Cancelado' },
                    { value: 'en proceso', text: 'En proceso' },
                    { value: 'finalizado', text: 'Finalizado' }
                ];

                opciones.forEach(opcion => {
                    const optionElement = document.createElement('option');
                    optionElement.value = opcion.value;
                    optionElement.text = opcion.text;
                    selectEstado.appendChild(optionElement);
                });
            }

            // Mostrar el modal
            document.getElementById('modalEstado').style.display = 'block';
        }
    };
});





    // Cambiar el cargo del cliente a administrador (modal)
    const botonesCambiarCargo = document.querySelectorAll('.btn-cambiar-cargo');
    botonesCambiarCargo.forEach((boton) => {
        boton.onclick = function() {
            const clienteId = this.getAttribute('data-id');
            const clienteNombre = this.getAttribute('data-nombre');
            document.getElementById('clienteId').value = clienteId;
            document.getElementById('clienteNombre').innerText = clienteNombre;
            document.getElementById('modalCambiarCargo').style.display = 'block';
        };
    });

    // Cerrar modal de cambiar cargo
    const cerrarModalCambiarCargo = document.getElementById('cerrarModal');
    if (cerrarModalCambiarCargo) {
        cerrarModalCambiarCargo.onclick = function() {
            document.getElementById('modalCambiarCargo').style.display = 'none';
        };
    }

// Enviar el formulario de cambio de cargo (promover o degradar)
const formCambiarCargo = document.getElementById('formCambiarCargo');
if (formCambiarCargo) {
    formCambiarCargo.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('userId', document.getElementById('clienteId').value);  // Usar el ID correcto
        formData.append('accion', 'promover'); // Acción de promoción

        const mensajeDiv = document.getElementById('mensaje-cambio-cargo');

        fetch('admin_cargo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mensajeDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
            } else {
                mensajeDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
            }
            if (data.success) {
                setTimeout(() => {
                    document.getElementById('modalCambiarCargo').style.display = 'none';
                    window.location.reload(); // Recargar para reflejar cambios
                }, 2000);
            }
        })
        .catch(error => {
            mensajeDiv.innerHTML = '<p style="color:red;">Error al procesar la solicitud.</p>';
        });
    };
}



    // Bajar el cargo del administrador a cliente (modal)
const botonesBajarCargo = document.querySelectorAll('.btn-bajar-cargo');
botonesBajarCargo.forEach((boton) => {
    boton.onclick = function() {
        const adminId = this.getAttribute('data-id');
        const adminNombre = this.getAttribute('data-nombre');
        document.getElementById('adminId').value = adminId;
        document.getElementById('adminNombre').innerText = adminNombre;
        document.getElementById('modalBajarCargo').style.display = 'block';
    };
});

// Cerrar modal de bajar cargo
const cerrarModalBajarCargo = document.getElementById('cerrarModalBajarCargo');
if (cerrarModalBajarCargo) {
    cerrarModalBajarCargo.onclick = function() {
        document.getElementById('modalBajarCargo').style.display = 'none';
    };
}

// Enviar el formulario de bajada de cargo
const formBajarCargo = document.getElementById('formBajarCargo');
if (formBajarCargo) {
    formBajarCargo.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('userId', document.getElementById('adminId').value);  // Usar el ID correcto
        formData.append('accion', 'degradar'); // Acción de degradación

        const mensajeDiv = document.getElementById('mensaje-bajar-cargo');

        fetch('admin_cargo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mensajeDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
            } else {
                mensajeDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
            }
            if (data.success) {
                setTimeout(() => {
                    document.getElementById('modalBajarCargo').style.display = 'none';
                    window.location.reload(); // Recargar para reflejar cambios
                }, 2000);
            }
        })
        .catch(error => {
            mensajeDiv.innerHTML = '<p style="color:red;">Error al procesar la solicitud.</p>';
        });
    };
}



        // Cargar horarios disponibles cuando se seleccione una fecha en el modal de reagendar
    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            const fechaSeleccionada = this.value;
            const horarioSelect = document.getElementById('horario');

            if (fechaSeleccionada && horarioSelect) {
                fetch('../formulario/obtener_horarios.php?date=' + fechaSeleccionada)
                    .then(response => response.json())
                    .then(horarios => {
                        horarioSelect.innerHTML = ''; // Limpiar las opciones anteriores
                        if (horarios.length > 0) {
                            horarios.forEach(horario => {
                                const option = document.createElement('option');
                                option.value = horario.id_horario;
                                option.textContent = horario.hora_disponible;
                                horarioSelect.appendChild(option);
                            });
                        } else {
                            horarioSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                        }
                    });
            }
        });
    }

// Función para gestionar el reagendamiento de citas con AJAX
const formReagendar = document.getElementById("formReagendar");
if (formReagendar) {
    formReagendar.onsubmit = function(e) {
        e.preventDefault(); // Evitar el envío tradicional del formulario

        const formData = new FormData(this); // Obtener los datos del formulario
        const mensajeReagendar = document.getElementById('mensaje-reagendar');

        // Mostrar mensaje de "procesando"
        mensajeReagendar.textContent = "Procesando, por favor espere...";
        mensajeReagendar.classList.remove("exito", "error"); // Limpiar cualquier clase previa
        mensajeReagendar.classList.add("procesando"); // Clase para estado de procesamiento

        // Enviar datos usando Fetch
        fetch("admin_reagendar.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json()) // Parsear respuesta JSON
        .then(data => {
            // Mostrar el mensaje basado en la respuesta
            mensajeReagendar.textContent = data.message;
            mensajeReagendar.classList.remove("procesando"); // Remover la clase de procesamiento

            if (data.success) {
                mensajeReagendar.classList.add("exito"); // Clase para éxito
                mensajeReagendar.classList.remove("error");

                // Ocultar el modal y limpiar el mensaje, luego recargar la página
                setTimeout(() => {
                    document.getElementById("modalReagendar").style.display = "none";
                    mensajeReagendar.textContent = ""; // Limpiar el mensaje
                    window.location.reload(); // Recargar la página después de 2 segundos
                }, 2000);
            } else {
                mensajeReagendar.classList.add("error"); // Clase para error
                mensajeReagendar.classList.remove("exito");
            }
        })
        .catch(error => {
            mensajeReagendar.textContent = "Ocurrió un error al procesar la solicitud.";
            mensajeReagendar.classList.remove("procesando");
            mensajeReagendar.classList.add("error");
        });
    };
}


// Función para gestionar el cambio de estado con AJAX
const formEstado = document.getElementById("formEstado");
if (formEstado) {
    formEstado.onsubmit = function(e) {
        e.preventDefault(); // Evitar el envío tradicional del formulario

        const formData = new FormData(this); // Obtener los datos del formulario
        const mensajeEstado = document.getElementById('mensaje-estado'); // Obtener el contenedor de mensaje

        // Mostrar mensaje de "procesando"
        mensajeEstado.textContent = "Procesando, por favor espere...";
        mensajeEstado.classList.remove("exito", "error"); // Limpiar cualquier clase previa
        mensajeEstado.classList.add("procesando"); // Clase para estado de procesamiento

        // Enviar datos usando Fetch
        fetch("admin_estado.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json()) // Parsear respuesta JSON
        .then(data => {
            // Mostrar el mensaje basado en la respuesta
            mensajeEstado.textContent = data.message;
            mensajeEstado.classList.remove("procesando"); // Remover la clase de procesamiento

            if (data.success) {
                mensajeEstado.classList.add("exito"); // Clase para éxito
                mensajeEstado.classList.remove("error");

                // Ocultar el modal y limpiar el mensaje, luego recargar la página
                setTimeout(() => {
                    document.getElementById("modalEstado").style.display = "none";
                    mensajeEstado.textContent = ""; // Limpiar el mensaje
                    window.location.reload(); // Recargar la página después de 2 segundos
                }, 2000);
            } else {
                mensajeEstado.classList.add("error"); // Clase para error
                mensajeEstado.classList.remove("exito");
                               // Ocultar el modal y limpiar el mensaje, luego recargar la página
                               setTimeout(() => {
                                document.getElementById("modalEstado").style.display = "none";
                                mensajeEstado.textContent = ""; // Limpiar el mensaje
                                window.location.reload(); // Recargar la página después de 2 segundos
                            }, 2000);
            }
        })
        .catch(error => {
            mensajeEstado.textContent = "Estado actualizado y correos enviados correctamente";
            mensajeEstado.classList.remove("procesando");
            mensajeEstado.classList.add("error");
        });
    };
}

    
 // Función para gestionar la configuración de cuenta con AJAX (debe estar dentro de rebindModalFunctions)
 const formConfiguracion = document.getElementById("configuracionForm");
 if (formConfiguracion) {
     formConfiguracion.onsubmit = function(e) {
         e.preventDefault(); // Evitar el envío tradicional del formulario

         const formData = new FormData(this); // Obtener los datos del formulario
         const mensajeConfiguracion = document.getElementById('mensaje-configuracion'); // Div donde se mostrará el mensaje

         // Enviar datos usando Fetch
         fetch("procesar_configuracion.php", {
             method: "POST",
             body: formData
         })
         .then(response => response.json()) // Parsear respuesta JSON
         .then(data => {
             // Mostrar el mensaje basado en la respuesta
             mensajeConfiguracion.textContent = data.message;
             if (data.success) {
                 mensajeConfiguracion.classList.add("exito"); // Clase para éxito
                 mensajeConfiguracion.classList.remove("error");
             } else {
                 mensajeConfiguracion.classList.add("error"); // Clase para error
                 mensajeConfiguracion.classList.remove("exito");
             }

             // Si la operación es exitosa, recargar la página después de 2 segundos
             if (data.success) {
                 setTimeout(() => {
                     location.reload(); // Recargar la página
                 }, 2000); // Recargar después de 2 segundos
             }
         })
         .catch(error => {
             mensajeConfiguracion.textContent = "Ocurrió un error al procesar la solicitud.";
             mensajeConfiguracion.classList.add("error");
         });
     };
    }

    



    // Función para cerrar el modal de detalles
    const closeButtonDetalles = document.querySelector(".cerrar-detalles");
    if (closeButtonDetalles) {
        closeButtonDetalles.onclick = function() {
            document.getElementById("modalDetalles").style.display = "none";
        };
    }

    // Función para cerrar el modal de presupuesto
    const closeButtonPresupuesto = document.querySelector(".cerrar-presupuesto");
    if (closeButtonPresupuesto) {
        closeButtonPresupuesto.onclick = function() {
            document.getElementById("modalPresupuesto").style.display = "none";
        };
    }
// Función para cerrar el modal de presupuesto aceptado
const cerrarModalPresupuestoAceptado = document.getElementById('cerrarPresupuestoAceptado');
if (cerrarModalPresupuestoAceptado) {
    cerrarModalPresupuestoAceptado.onclick = function() {
        document.getElementById('modalPresupuestoAceptado').style.display = 'none';
    };
}


// Función para cerrar el modal de presupuesto pendiente
const cerrarModalPresupuestoPendiente = document.getElementById('cerrarPresupuestoPendiente');
if (cerrarModalPresupuestoPendiente) {
    cerrarModalPresupuestoPendiente.onclick = function() {
        document.getElementById('modalPresupuestoPendiente').style.display = 'none';
    };
}



    // Función para cerrar el modal de reagendar
    const closeButtonReagendar = document.querySelector(".cerrar-reagendar");
    if (closeButtonReagendar) {
        closeButtonReagendar.onclick = function() {
            document.getElementById("modalReagendar").style.display = "none";
        };
    }

    // Función para cerrar el modal de cambiar estado
    const closeButtonEstado = document.querySelector(".cerrar-estado");
    if (closeButtonEstado) {
        closeButtonEstado.onclick = function() {
            document.getElementById("modalEstado").style.display = "none";
        };
    }

    // Cerrar modales al hacer clic fuera de ellos
    window.onclick = function(event) {
        const modalDetalles = document.getElementById("modalDetalles");
        const modalPresupuesto = document.getElementById("modalPresupuesto");
        const modalPresupuestoAceptado = document.getElementById("modalPresupuestoAceptado");
        const modalPresupuestoPendiente = document.getElementById("modalPresupuestoPendiente");
        const modalReagendar = document.getElementById("modalReagendar");
        const modalEstado = document.getElementById("modalEstado");
        const modalCambiarCargo = document.getElementById("modalCambiarCargo");

        if (event.target == modalDetalles) {
            modalDetalles.style.display = "none";
        }
        if (event.target == modalPresupuesto) {
            modalPresupuesto.style.display = "none";
        }
        if (event.target == modalPresupuestoAceptado) {
            modalPresupuestoAceptado.style.display = "none";
        }
        if (event.target == modalPresupuestoPendiente) {
            modalPresupuestoPendiente.style.display = "none";
        }
        if (event.target == modalReagendar) {
            modalReagendar.style.display = "none";
        }
        if (event.target == modalEstado) {
            modalEstado.style.display = "none";
        }
        if (event.target == modalCambiarCargo) {
            modalCambiarCargo.style.display = "none";
        }
    };
}

// Función para mostrar detalles de la cita en el modal
// Función para mostrar detalles de la cita en el modal
function mostrarDetalles(detallesCita) {
    var contenidoDetalles = document.getElementById("contenidoDetalles");
    contenidoDetalles.innerHTML = `
        <p><strong>ID Formulario:</strong> ${detallesCita.id_form}</p>
        <p><strong>Nombre:</strong> ${detallesCita.nombre} ${detallesCita.apellido}</p>
        <p><strong>RUT:</strong> ${detallesCita.rut}</p> <!-- Mostrar el RUT aquí -->
        <p><strong>Correo:</strong> ${detallesCita.correo}</p>
        <p><strong>Dirección:</strong> ${detallesCita.direccion}</p>
        <p><strong>Teléfono:</strong> ${detallesCita.telefono}</p>
        <p><strong>Detalles:</strong> ${detallesCita.detalles}</p>
        <p><strong>Estado:</strong> ${detallesCita.estado}</p>
        <p><strong>Fecha de la Cita:</strong> ${detallesCita.fecha}</p>
        <p><strong>Hora de la Cita:</strong> ${detallesCita.hora_disponible}</p>
    `;
    document.getElementById("modalDetalles").style.display = "block";
}


// Event listener para cargar clientes
document.getElementById('ver-clientes').addEventListener('click', function (e) {
    e.preventDefault();  // Evitar comportamiento de navegación
    loadContent('ver_clientes.php');  // Cargar el archivo ver_clientes.php
});

// Event listener para cargar todas las citas
document.getElementById('ver-citas').addEventListener('click', function (e) {
    e.preventDefault();
    verTodasCitas();  // Cargar todas las citas
});

// Función para cargar configuración de cuenta
document.querySelector('[id="configuracion"]').addEventListener('click', function (e) {
    e.preventDefault();
    loadContent('admin_configuracion.php');
});

// Cargar la lista de administradores
document.getElementById('ver-administradores').addEventListener('click', function (e) {
    e.preventDefault();
    loadContent('ver_administradores.php');  // Cargar el archivo ver_administradores.php
});


document.getElementById('insertar-horarios').addEventListener('click', function (e) {
    e.preventDefault();
    loadContent('admin_fechas.php'); // Cargar el formulario de fechas

    setTimeout(function() {
        const insertarFechasForm = document.getElementById('insertarFechasForm');
        if (insertarFechasForm) {
            insertarFechasForm.onsubmit = function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const mensajeDiv = document.getElementById('mensaje');

                // Enviar los datos usando Fetch
                fetch('procesar_fechas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mensajeDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                    } else {
                        mensajeDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    mensajeDiv.innerHTML = '<p style="color:red;">Error al insertar fechas.</p>';
                });
            };
        }
    }, 500); // Asegurarse de que el DOM esté cargado
});


