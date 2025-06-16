/**
 * Variables globales
 */
var idCliente = null; // ID del cliente autenticado
var idContrato = null; // ID del contrato del cliente (si existe)
var servicioSeleccionadoId = null; // ID del servicio que el usuario desea reservar
var especialidadSeleccionadaId = null; // ID de la especialidad asociada al servicio
var empleadosAsignados = [];           // Lista de empleados capaces para el servicio actual
var franjasHorariasDisponibles = [];   // Array de horas disponibles (ej: ["09:00", "09:30", ...])
var disponibilidadPorHora = {};        // Objeto: { "09:00": [ {id,name}, ... ], ... }


/**
 * Carga el perfil de cliente autenticado y obtiene el ID de cliente y el ID de contrato.
 */
async function obtenerPerfilCliente() {
    try {
        // Obtener el token de autorización del almacenamiento local del navegador
        const token = localStorage.getItem('token');

        // Realizar la solicitud a la API para obtener el perfil del usuario
        const respuesta = await fetch('http://localhost:8001/api/profile', {
            headers: {
                'Authorization': 'Bearer ' + token, // Incluir el token en la cabecera para autenticación
                'Accept':        'application/json' // Indicar que esperamos una respuesta en formato JSON
            }
        });
        const datos = await respuesta.json(); // Convertir la respuesta del servidor a un objeto JavaScript

        // Verificar si la respuesta fue exitosa (código 2xx) y si hay datos de cliente
        if (respuesta.ok && datos.cliente) {
            idCliente = datos.cliente.id; // Asignar el ID del cliente a la variable global

            // Verificar si existe un contrato y asignar su ID a la variable global
            if (datos.contrato && datos.contrato.id) {
                idContrato = datos.contrato.id;
            } else {
                idContrato = null; // Si no hay contrato, establecerlo como nulo
            }
        } else {
            // Si el perfil de cliente no se encuentra o la respuesta no es válida, mostrar un error
            console.error('Perfil de cliente no encontrado', datos);
            // Usar alert solo para fines de ejemplo, se recomienda reemplazar con una UI modal más amigable
            alert('Necesitas completar tu perfil para reservar.');
        }
    } catch (error) {
        // Capturar y manejar cualquier error que ocurra durante la carga del perfil
        console.error('Error al cargar perfil de cliente', error);
        // Usar alert solo para fines de ejemplo, se recomienda reemplazar con una UI modal más amigable
        alert('Error al cargar perfil de cliente.');
    }
}

/**
 * Vincula el evento 'click' de los botones “Reservar” presentes en cada tarjeta de servicio.
 */
function activarBotonesReservar() {
    // Seleccionar todos los botones con la clase 'reservar-btn'
    const botonesReservar = document.querySelectorAll('.reservar-btn');

    // Iterar sobre cada botón de reserva y asignar una función asíncrona al evento 'onclick'
    botonesReservar.forEach(boton => {
        boton.onclick = async () => {
            // Obtener la tarjeta de servicio padre del botón clickeado para acceder a sus datos
            const tarjetaServicio = boton.parentNode;

            // Extraer y asignar los IDs y datos del servicio a las variables globales
            servicioSeleccionadoId      = Number(tarjetaServicio.dataset.serviceId);
            especialidadSeleccionadaId  = Number(tarjetaServicio.dataset.especialidadId);
            const nombreDelServicio     = tarjetaServicio.dataset.nombreServicio;
            const duracionDelServicio   = tarjetaServicio.dataset.duracionServicio;
            const precioDelServicio     = tarjetaServicio.dataset.precioServicio;

            // Rellenar los elementos del modal de reserva con los datos del servicio seleccionado
            document.getElementById('modalServiceTitle').innerText  = `Reserva: ${nombreDelServicio}`;
            document.getElementById('modalServiceName').innerText   = nombreDelServicio;
            document.getElementById('modalDuration').innerText      = duracionDelServicio;
            document.getElementById('modalPrice').innerText         = precioDelServicio;

            // Parsear la lista de empleados asignados al servicio (desde un atributo de datos JSON)
            empleadosAsignados = JSON.parse(tarjetaServicio.dataset.empleadosAsignados || '[]');

            // Llenar el elemento <select> de empleados dentro del modal
            llenarSelectEmpleados();

            // Configurar la fecha mínima del input de fecha a hoy (en la zona horaria de España)
            configurarFechaMinima();

            // Limpiar el contenedor donde se mostrarán las franjas horarias disponibles
            document.getElementById('timeSlots').innerHTML = '';

            // Abrir el modal de reserva
            abrirModalReserva();

            // Pintar de inmediato las franjas horarias disponibles para el empleado seleccionado por defecto
            await refrescarFranjasDisponibles();

            // Vincular los eventos 'onchange' de la fecha y el desplegable de empleados
            // para que se refresquen las franjas cuando cambie la selección
            document.getElementById('resDate').onchange     = refrescarFranjasDisponibles;
            document.getElementById('empleadoSelect').onchange = refrescarFranjasDisponibles;
        };
    });
}

/**
 * Llena el <select id="empleadoSelect"> con los empleadosAsignados.
 */
function llenarSelectEmpleados() {
    const selectEmpleados = document.getElementById('empleadoSelect');
    selectEmpleados.innerHTML = ''; // Limpiamos las opciones existentes en el select

    // Si no hay empleados asignados al servicio, mostrar una opción por defecto
    if (empleadosAsignados.length === 0) {
        selectEmpleados.innerHTML = '<option value="">— Sin empleados asignados —</option>';
        return;
    }

    // Iterar sobre la lista de empleados asignados y añadir cada uno como una opción al select
    empleadosAsignados.forEach(empleado => {
        const option = document.createElement('option');
        option.value = empleado.id; // El valor de la opción será el ID del empleado
        option.text  = empleado.name; // El texto visible de la opción será el nombre del empleado
        selectEmpleados.appendChild(option);
    });
}

/**
 * Establece la fecha mínima del <input type="date" id="resDate"> a hoy,
 * asegurando que 'hoy' se calcule en la zona horaria de España (Europe/Madrid).
 */
function configurarFechaMinima() {
    const dateInput = document.getElementById('resDate');

    // Obtener la fecha actual en la zona horaria de Madrid y formatearla como "DD/MM/YYYY"
    const fechaActualEnMadrid = new Date().toLocaleDateString('es-ES', {
        timeZone: 'Europe/Madrid',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });

    // Convertir el formato "DD/MM/YYYY" a "YYYY-MM-DD", que es el formato esperado por el input type="date"
    const [dia, mes, anio] = fechaActualEnMadrid.split('/');
    const hoyFormatoISO = `${anio}-${mes}-${dia}`;

    // Establecer la fecha mínima del input si no se ha hecho ya (para evitar reconfigurar en cada apertura de modal)
    if (!dateInput.hasAttribute('data-min-set')) {
        dateInput.min = hoyFormatoISO;
        dateInput.setAttribute('data-min-set', 'true'); // Marcar que la mínima ya ha sido establecida
    }
    // Establecer el valor del input a la fecha de hoy si aún no tiene un valor
    if (!dateInput.value) {
        dateInput.value = hoyFormatoISO;
    }
}

/**
 * Abre el modal de reserva configurando su estilo 'display' a 'flex'.
 */
function abrirModalReserva() {
    const modal = document.getElementById('reservaModal');
    // Guardar el ID del servicio seleccionado en un atributo de datos del modal
    modal.dataset.serviceId = servicioSeleccionadoId;
    modal.style.display = 'flex'; // Hace que el modal sea visible
}

/**
 * Cierra el modal de reserva configurando su estilo 'display' a 'none'.
 */
function cerrarModalReserva() {
    const modal = document.getElementById('reservaModal');
    modal.style.display = 'none'; // Oculta el modal
}

/**
 * Obtiene las franjas disponibles desde la API, las filtra por disponibilidad
 * y por la hora actual (si la fecha seleccionada es hoy en España).
 */
async function refrescarFranjasDisponibles() {
    // 1. Obtener la fecha seleccionada del input y el contenedor de franjas horarias
    const fechaSeleccionada = document.getElementById('resDate').value;
    const contenedorDeFranjas = document.getElementById('timeSlots');

    // Si no hay fecha seleccionada en el input, limpiar el contenedor y salir de la función
    if (!fechaSeleccionada) {
        contenedorDeFranjas.innerHTML = '';
        return;
    }

    // Mostrar un mensaje de carga al usuario mientras se obtienen los datos
    contenedorDeFranjas.innerText = 'Cargando franjas…';

    try {
        // 2. Obtener el token de autorización del almacenamiento local
        const tokenDeAutorizacion = localStorage.getItem('token');

        // Realizar la solicitud a la API para obtener las franjas horarias disponibles para el servicio y fecha seleccionados
        const respuestaApi = await fetch(
            `http://localhost:8001/api/services/${servicioSeleccionadoId}/available-slots?fecha=${fechaSeleccionada}`, // ¡Corregido aquí!
            { headers: { 'Authorization': 'Bearer ' + tokenDeAutorizacion, 'Accept': 'application/json' } }
        );
        const datosDeFranjas = await respuestaApi.json(); // Convertir la respuesta a JSON

        // Si la respuesta de la API no fue exitosa (ej. error 404, 500), mostrar un mensaje de error
        if (!respuestaApi.ok) {
            contenedorDeFranjas.innerText = 'Error al cargar franjas';
            return;
        }

        // Obtener los datos de los empleados y sus franjas de la respuesta de la API
        const empleadosConFranjas = datosDeFranjas.employees;

        // 3. Preparar la lógica para filtrar franjas horarias pasadas, usando la hora local de España.
        // Obtener la fecha actual formateada en la zona horaria de Madrid (ej. "10/06/2025")
        const hoyEnMadrid = new Date().toLocaleDateString('es-ES', { timeZone: 'Europe/Madrid' });
        // Obtener la fecha seleccionada del input, también formateada en la zona horaria de Madrid
        const fechaSeleccionadaFormateada = new Date(fechaSeleccionada).toLocaleDateString('es-ES', { timeZone: 'Europe/Madrid' });

        // Determinar si la fecha seleccionada por el usuario ES HOY en la zona horaria de Madrid
        const esHoyEnMadrid = fechaSeleccionadaFormateada === hoyEnMadrid;

        let horaActualEnMadrid = null;
        let minutoActualEnMadrid = null;

        // Si la fecha seleccionada es hoy en Madrid, obtener la hora y minutos actuales en esa zona horaria
        if (esHoyEnMadrid) {
            const fechaHoraActual = new Date(); // Objeto Date basado en la hora local del sistema del usuario
            // Convertir la fecha y hora actual del sistema a la zona horaria de Madrid para una comparación precisa
            const fechaHoraEnMadrid = new Date(fechaHoraActual.toLocaleString('en-US', { timeZone: 'Europe/Madrid' }));
            horaActualEnMadrid = fechaHoraEnMadrid.getHours();
            minutoActualEnMadrid = fechaHoraEnMadrid.getMinutes();
        }

        // 4. Rellenar el desplegable (select) de empleados con solo los que tengan franjas válidas (no pasadas).
        const selectorDeEmpleados = document.getElementById('empleadoSelect');
        selectorDeEmpleados.innerHTML = ''; // Limpiar las opciones existentes del select

        const empleadosFiltrados = {}; // Objeto temporal para almacenar los empleados con sus franjas ya filtradas

        // Iterar sobre cada empleado devuelto por la API
        Object.entries(empleadosConFranjas).forEach(([idEmpleado, datosEmpleado]) => {
            // Filtrar las franjas horarias de cada empleado
            const franjasHorariasFiltradas = datosEmpleado.slots.filter(franjaHora => {
                // Si la fecha seleccionada NO es hoy, todas las franjas son válidas
                if (!esHoyEnMadrid) return true;

                // Si es hoy, dividimos la franja de hora (ej. "09:30" se convierte en 9 y 30)
                const [horaFranja, minutoFranja] = franjaHora.split(':').map(Number);

                // Comprobar si la franja horaria ya ha pasado respecto a la hora actual en Madrid
                // Si la hora de la franja es menor que la hora actual, ya pasó
                if (horaFranja < horaActualEnMadrid) {
                    return false;
                }
                // Si la hora es la misma, pero el minuto de la franja es menor o igual al minuto actual, ya pasó
                if (horaFranja === horaActualEnMadrid && minutoFranja <= minutoActualEnMadrid) {
                    return false;
                }
                // Si no ha pasado, la franja es válida
                return true;
            });

            // Si el empleado tiene franjas filtradas (es decir, franjas válidas y no pasadas), añadirlo al selector
            if (franjasHorariasFiltradas.length > 0) {
                const opcion = document.createElement('option');
                opcion.value = idEmpleado; // El valor de la opción será el ID del empleado
                opcion.text  = datosEmpleado.name; // El texto visible será el nombre del empleado
                selectorDeEmpleados.appendChild(opcion);
                // Guardar las franjas filtradas para este empleado en el objeto temporal
                empleadosFiltrados[idEmpleado] = { ...datosEmpleado, slots: franjasHorariasFiltradas };
            }
        });

        // 5. Definir una función interna para pintar las franjas horarias de un empleado específico.
        // Esta función se llamará cuando se seleccione un empleado.
        function pintarFranjasParaEmpleado(idDelEmpleado) {
            contenedorDeFranjas.innerHTML = ''; // Limpiar el contenedor antes de pintar nuevas franjas

            // Obtener las franjas del empleado seleccionado (ya filtradas)
            const franjasDelEmpleado = empleadosFiltrados[idDelEmpleado] ? empleadosFiltrados[idDelEmpleado].slots : [];

            // Si no hay franjas disponibles para este empleado (después del filtrado), mostrar un mensaje
            if (franjasDelEmpleado.length === 0) {
                contenedorDeFranjas.innerText = 'No hay franjas disponibles para este empleado';
                return;
            }

            // Crear y añadir cada franja horaria como un elemento 'div' clicable
            franjasDelEmpleado.forEach(franjaHora => {
                const elementoFranja = document.createElement('div');
                elementoFranja.className = 'timeslot'; // Clase CSS para estilizar el elemento
                elementoFranja.innerText = franjaHora; // Mostrar la hora de la franja

                // Asignar el evento 'onclick' a cada franja para manejar la selección
                elementoFranja.onclick = () => {
                    // Remover la clase 'selected' de todas las franjas para deseleccionar las anteriores
                    document.querySelectorAll('.timeslot').forEach(elemento => elemento.classList.remove('selected'));
                    // Añadir la clase 'selected' a la franja clickeada para marcarla como seleccionada
                    elementoFranja.classList.add('selected');
                };
                contenedorDeFranjas.appendChild(elementoFranja); // Añadir el elemento al contenedor
            });
        }

        // 6. Configurar el evento 'onchange' para el selector de empleados:
        // Cuando se cambie el empleado seleccionado, repintar las franjas horarias.
        selectorDeEmpleados.onchange = () => pintarFranjasParaEmpleado(selectorDeEmpleados.value);

        // 7. Pintar las franjas iniciales:
        // Si hay un empleado seleccionado (el primero que tenga franjas válidas), pintar sus franjas.
        if (selectorDeEmpleados.value) {
            pintarFranjasParaEmpleado(selectorDeEmpleados.value);
        } else {
            // Si no hay empleados con franjas válidas para esta fecha, mostrar un mensaje
            contenedorDeFranjas.innerText = 'No hay empleados con franjas disponibles para esta fecha.';
        }

    } catch (error) {
        // 8. Capturar y manejar cualquier error durante la carga o procesamiento de franjas
        console.error('Error cargando franjas', error);
        contenedorDeFranjas.innerText = 'Error al cargar franjas';
    }
}

/**
 * Envía la solicitud para confirmar la reserva, validando fecha, hora y empleado.
 * Esta función también deshabilita el botón de confirmación durante el proceso
 * para evitar envíos duplicados.
 */
async function confirmarReserva() {
    // 1. Obtener la referencia al botón de confirmar reserva
    const botonConfirmar = document.getElementById('confirmReservaBtn');

    // 2. Deshabilitar el botón y cambiar su texto para indicar que se está procesando
    // Esto previene que el usuario haga clic repetidamente mientras la operación está en curso.
    botonConfirmar.disabled = true;
    botonConfirmar.innerText = 'Reservando...';

    // 3. Validar que el ID del cliente esté cargado. Si no, mostrar una alerta y salir.
    if (!idCliente) { // Usamos idCliente (tu variable global original)
        alert('Perfil de cliente no cargado. Por favor, intenta de nuevo.'); // Usar alert solo para fines de ejemplo, reemplazar con UI modal
        // No hay un 'return' aquí, porque el 'finally' se encargará de re-habilitar el botón.
    }

    // 4. Verificar si existe un contrato. Si no, intentar crear uno.
    if (!idContrato && idCliente) { // Usamos idContrato e idCliente (tus variables globales originales)
        try {
            const tokenDeAutorizacion = localStorage.getItem('token');
            const respuestaCrearContrato = await fetch(
                `http://localhost:8001/api/customers/${idCliente}/contracts`,
                {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'Authorization': 'Bearer ' + tokenDeAutorizacion
                    },
                    body: JSON.stringify({ numero_de_atenciones: 1 }) // Datos para crear el contrato
                }
            );

            if (!respuestaCrearContrato.ok) {
                // Si la creación del contrato falla, capturar el error y lanzar una excepción.
                const datosError = await respuestaCrearContrato.json().catch(() => null);
                console.error('Error creando contrato:', datosError || respuestaCrearContrato.statusText);
                throw new Error('No se pudo crear tu contrato. Intenta de nuevo.');
            }

            const datosNuevoContrato = await respuestaCrearContrato.json();
            idContrato = datosNuevoContrato.id; // Asignar el ID del contrato recién creado a la variable global

        } catch (error) {
            console.error('Error al intentar crear contrato', error);
            alert('Error al crear contrato. Por favor, revisa la consola para más detalles.'); // Usar alert solo para fines de ejemplo, reemplazar con UI modal
            // Lanzamos la excepción para que el bloque 'catch' principal la capture
            throw error;
        }
    }

    // Asegurarse de que tenemos un contrato antes de continuar con la reserva.
    if (!idContrato) { // Usamos idContrato
        alert('No se pudo establecer un contrato para la reserva. Intenta de nuevo.');
        // Si llegamos aquí y no hay contrato, significa que hubo un error y el botón debe re-habilitarse.
        // El bloque 'finally' se encargará de esto.
        return; // Salir de la función si no hay contrato.
    }

    // 5. Validar que se hayan seleccionado la fecha, hora y empleado
    const inputFechaSeleccionada    = document.getElementById('resDate');
    const fechaDeReserva            = inputFechaSeleccionada.value;
    const elementoHoraSeleccionada  = document.querySelector('.timeslot.selected'); // El elemento HTML de la franja horaria seleccionada
    const selectorDeEmpleados       = document.getElementById('empleadoSelect');
    const idEmpleadoSeleccionado    = selectorDeEmpleados ? selectorDeEmpleados.value : null; // ID del empleado seleccionado

    if (!fechaDeReserva || !elementoHoraSeleccionada || !idEmpleadoSeleccionado) {
        alert('Por favor, selecciona una fecha, una hora y un empleado para la reserva.'); // Usar alert solo para fines de ejemplo, reemplazar con UI modal
        return; // Salir si falta alguna selección
    }

    // 6. Preparar los datos de la reserva para enviar a la API
    const horaSeleccionadaTexto = elementoHoraSeleccionada.innerText; // Ej: "09:30"
    // Combinar la fecha y la hora para formar una cadena de fecha y hora completa (ej: "2025-06-10 09:30:00")
    // Se asume que el backend manejará esta cadena correctamente en su contexto (probablemente UTC o zona del servidor).
    const fechaHoraCompleta = `${fechaDeReserva} ${horaSeleccionadaTexto}:00`;
    const idServicioParaReserva = servicioSeleccionadoId; // Usamos servicioSeleccionadoId (tu variable global original)

    const datosDeLaReserva = {
        contrato_id:            idContrato, // Usamos idContrato
        cliente_id:             idCliente, // Usamos idCliente
        empleado_id:            Number(idEmpleadoSeleccionado), // Asegurarse de que es un número
        servicio_id:            idServicioParaReserva,
        fecha:                  fechaHoraCompleta,
        estado:                 'pendiente', // Estado inicial de la reserva
        numero_de_atenciones:   1 // Número de atenciones para esta reserva
    };

    // 7. Enviar la solicitud POST para crear la reserva
    try {
        const tokenDeAutorizacion = localStorage.getItem('token');
        const respuestaReserva = await fetch('http://localhost:8001/api/appointments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'Authorization':'Bearer ' + tokenDeAutorizacion
            },
            body: JSON.stringify(datosDeLaReserva) // Convertir el objeto de datos a JSON y enviarlo
        });

        // 8. Manejar la respuesta de la API
        if (!respuestaReserva.ok) {
            // Si la respuesta no fue exitosa (código de estado 4xx o 5xx), lanzar un error
            const datosError = await respuestaReserva.json().catch(() => null); // Intentar obtener detalles del error
            console.error('Error creando la cita:', datosError || respuestaReserva.statusText);
            throw new Error('Falló la creación de la reserva.');
        }

        // Si la reserva fue exitosa:
        alert('¡Reserva confirmada con éxito!'); // Usar alert solo para fines de ejemplo, reemplazar con UI modal
        cerrarModalReserva(); // Cerrar el modal de reserva

    } catch (error) {
        // 9. Capturar y manejar cualquier error que ocurra durante la solicitud o validación
        console.error('Error al intentar reservar', error);
        alert('Error al reservar. Por favor, revisa la consola para más detalles.'); // Usar alert solo para fines de ejemplo, reemplazar con UI modal

    } finally {
        // 10. Bloque 'finally': Siempre se ejecuta, independientemente de si hubo éxito o error.
        // Esto garantiza que el botón siempre vuelva a su estado original.
        botonConfirmar.disabled = false; // El botón vuelve a ser clicable
        botonConfirmar.innerText = 'Confirmar Reserva'; // Se restaura el texto original
    }
}

/**
 * Vincula el evento del botón “Confirmar Reserva”.
 */
const botonConfirmar = document.getElementById('confirmReservaBtn');
if (botonConfirmar) {
    botonConfirmar.onclick = confirmarReserva;
}

/**
 * Vincula el evento de cerrar modal al hacer clic fuera o en la X.
 */
const botonCerrar = document.getElementById('closeReserva');
if (botonCerrar) {
    botonCerrar.onclick = cerrarModalReserva;
}
window.onclick = event => {
    if (event.target.id === 'reservaModal') {
        cerrarModalReserva();
    }
};

/**
 * Inicialización al cargar la página
 */
document.addEventListener('DOMContentLoaded', async () => {
    await obtenerPerfilCliente();
    activarBotonesReservar(); // Asegúrate de llamar a esta función para que los botones de reservar funcionen.
});
