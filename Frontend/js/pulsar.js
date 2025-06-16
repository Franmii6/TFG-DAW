// common.js

/**
 * Toma la lista completa de citas y, para cada cita que esté en estado "pendiente"
 * y cuya fecha ya sea pasada (fecha <= ahora), manda un PUT para marcarla como "completada".
 * De ese modo, el backend actualizará a la vez el contrato asociado.
 *
 * SOLO se ejecuta si hay un token en localStorage (es decir, alguien está autenticado).
 */
async function autoCompletarCitas() {
  // 0) Solo proceder si hay token (usuario loggeado)
  const token = localStorage.getItem('token');
  if (!token) {
    // No hay usuario autenticado → no llamamos a las rutas protegidas
    return;
  }

  try {
    // 1) Obtener todas las citas del sistema (ruta protegida)
    const resAll = await fetch('http://localhost:8001/api/appointments', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept':        'application/json'
      }
    });

    if (!resAll.ok) {
      // Si devuelve 401/403/500, mostramos una advertencia y salimos
      console.warn('autoCompletarCitas: no se pudieron obtener todas las citas →', resAll.status);
      return;
    }

    const todasLasCitas = await resAll.json(); // Array de objetos { id, fecha, estado, ... }

    // 2) Recorremos cada cita y comprobamos si debe pasar a "completada"
    const ahora = new Date();

    for (const cita of todasLasCitas) {
      // Solo nos interesan las que están en "pendiente"
      if (cita.estado.trim().toLowerCase() !== 'pendiente') {
        continue;
      }

      // Convertimos la propiedad "fecha" a Date
      const fechaCita = new Date(cita.fecha);
      if (fechaCita <= ahora) {
        // Ya pasó la hora: marcamos como "completada"
        try {
          const updateRes = await fetch(`http://localhost:8001/api/appointments/${cita.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type':  'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ estado: 'completada' })
          });

          if (!updateRes.ok) {
            console.warn(`autoCompletarCitas: no se pudo actualizar cita #${cita.id} →`, updateRes.status);
          } else {
            console.log(`autoCompletarCitas: cita #${cita.id} marcada como completada.`);
          }
        } catch (err) {
          console.error(`autoCompletarCitas: error al actualizar cita #${cita.id} →`, err);
        }
      }
    }
  } catch (err) {
    console.error('autoCompletarCitas: error general →', err);
  }
}

/**
 * Al cargar cualquier página, arrancamos la primera pasada (si hay usuario)
 * y luego disparamos autoCompletarCitas cada 60 segundos, siempre que siga habiendo token.
 */
document.addEventListener('DOMContentLoaded', () => {
  // 1) Ejecutar inmediatamente si hay token
  autoCompletarCitas();

  // 2) Repetir cada 60 segundos
  setInterval(autoCompletarCitas, 60 * 1000);
});

/*
  NOTAS DE IMPLEMENTACIÓN:

  • Comprobamos localStorage.getItem('token') antes de llamar a cualquier ruta protegida.
    Así, si nadie está loggeado, autoCompletarCitas simplemente regresa sin nuevos fetches.

  • Cuando hay un token, hacemos un GET a /api/appointments (route protegida),
    iteramos sobre todas las citas devueltas:
      – Si "estado" es distinto de "pendiente", lo ignoramos.
      – Si "estado" es "pendiente" y la fecha de la cita ya ha pasado, enviamos un PUT a
        /api/appointments/{id} con { estado: "completada" }.

    El AppointmentController@update en el backend se encargará de incrementar automáticamente
    el contador de atenciones en el contrato correspondiente y marcar el contrato como
    "finalizado" cuando toque.

  • Ponemos este bloque dentro de DOMContentLoaded y un setInterval para que, mientras la
    página esté abierta y haya un usuario (cualquier usuario, admin o cliente), se actualice
    cada minuto.

  • De esta forma no es necesario exponer nuevas rutas ni cambiar el backend en gran escala,
    y las llamadas protegidas solo se intentan cuando hay un usuario autenticado en localStorage.
*/
