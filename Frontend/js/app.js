// FUNCIÓN DE REGISTRO
async function register(event) { //Asyn function para manejar la promesa de fetch, usar await y mejorar la legibilidad del código
  event.preventDefault(); //Prevenir que se recargue la página

  var btn = event.target.querySelector('button[type="submit"]'); // Botón de envío
  var orig = btn.innerText; // 
  btn.disabled = true;
  btn.innerText = 'Registrando…';
  //Guardar los valores del formulario

  var nombre                = document.getElementById('nombre').value;
  var nombreUsuario         = document.getElementById('nombreUsuario').value;
  var email                 = document.getElementById('email').value;
  var contrasena            = document.getElementById('contrasena').value;
  var contrasena_confirmation = document.getElementById('contrasena_confirmation').value;

  if (contrasena !== contrasena_confirmation) { //Validar la contraseña en el frontend
    alert('¡Las contraseñas no coinciden!');
    return;
  }

  try { //Hago un try catch para manejar errores
    // Envio la petición al servidor
    var res = await fetch('http://localhost:8001/api/register', { //Uso await para esperar la respuesta de la promesa
      method: 'POST',
      headers: {
        'Content-Type': 'application/json', //Content-type para enviar el body en formato JSON
        'Accept':        'application/json' //Accept para recibir la respuesta en formato JSON
      },
      body: JSON.stringify({ nombre, nombreUsuario, email, contrasena, contrasena_confirmation }) // Uso JSON.stringify para convertir el objeto a string, y pasar todas las credenciales del formulario
    });

    var data = await res.json(); //Creo la variable data para guardar la respuesta del servidor en formato JSON, uso await de nuevo para esperar la respuesta de la promesa
    if (!res.ok) { //Si la respuesta no es ok, significa que hubo un error
      // Si hay errores, los muestro en un alert
      var msg = data.errors //Los guardo en una variable
        ? Object.values(data.errors).flat().join('\n') //Los convierto a un string separados por un salto de línea
        : (data.message || 'Error en registro'); //Si no hay errores, muestro el mensaje de error o un mensaje por defecto
      alert(`Registro fallido:\n${msg}`); //Muestro el mensaje de error en un alert
      return;
    }

    alert('¡Registro exitoso! Ahora puedes iniciar sesión.'); //Si el registro es exitoso, muestro un mensaje de éxito
    window.location.href = 'login.html'; //Redirijo a la página de login

  } catch (err) { //Si hay un error en la petición, lo muestro en la consola y muestro un mensaje de error
    console.error('Error en el registro:', err); //Mostrado por consola
    alert('Error de red o servidor. Revisa la consola.'); //Hago un alert para decirle al usuario que revise la consola
  }
  finally {
    btn.disabled = false;
    btn.innerText = orig;
  }
}

// FUNCIÓN DE LOGIN
async function login(event) {
  event.preventDefault(); //Evito que se recargue la página

  var btn = event.target.querySelector('button[type="submit"]'); // Botón de envío
  var orig = btn.innerText; // 
  btn.disabled = true;
  btn.innerText = 'Registrando…';
  
  //Guardo en una variable el email y la contraseña
  var email      = document.getElementById('email').value;
  var contrasena = document.getElementById('contrasena').value;

  try { //Hago un try catch para manejar errores
    var res = await fetch('http://localhost:8001/api/login', { //Guardo en una variable la respuesta del servidor, usando await para esperar la respuesta de la promesa
      method: 'POST', //Método POST para enviar los datos al servidor
      headers: {
        'Content-Type': 'application/json', //Content-type para enviar el body en formato JSON
        'Accept':        'application/json' //Accept para recibir la respuesta en formato JSON
      },
      body: JSON.stringify({ email, contrasena }) 
    });

    var data = await res.json(); //Guardo en una variable la respuesta del servidor usando de nuevo await para esperar la respuesta de la promesa

    if (!res.ok) { //Si la respuesta no es ok, significa que hubo un error
      alert(data.message || 'Error en login'); //Lo muestro con un alert
      return;
    }

    // Mostrar mensaje de éxito si el login es exitoso
    alert('¡Login exitoso! Bienvenido, ' + data.user.nombreUsuario); //Muestro un mensaje de éxito con el nombre de usuario

    // Guardar el token en localStorage para usarlo en futuras peticiones
    console.log('Token recibido:', data.token); // Para verificar que sí llega el token
    localStorage.setItem('token', data.token);
    localStorage.setItem('token_time', Date.now()); //Guardo el día en el que hace login, para saber el tiempo que lleva el token activo
    // ▲ GUARDAMOS el nombre de usuario para montar el nav sin más fetches
    localStorage.setItem('username', data.user.nombreUsuario);

    if (data.user === 'Admin' && data.email === 'admin@barberia.com') {
          window.location.href = 'admin.html'; //Redirijo a la página de admin si es admin
    } else {
      window.location.href = 'index.html'; //Sino, redirijo a la página de inicio
    }


  } catch (err) { //Capturo el error si lo hubiese
    console.error('Error en login:', err); //Lo muestro por consola
    alert('Error de red o servidor. Revisa la consola.'); //Mando un mensaje por alert para que revise el error por consola.
  }
}

// FUNCIÓN PARA INICIALIZAR LA AUTENTICACIÓN
// — Protege sólo perfil.html (redirige si no hay sesión o ha expirado).
async function initAuth() {

  // 2) Leer token y caducidad
  const token     = localStorage.getItem('token');
  const tokenTime = Number(localStorage.getItem('token_time')) || 0;
  const MAX_TIME  = 1000 * 60 * 60 * 24 * 7; // 7 días  
  const path      = window.location.pathname;

  // 3) Proteger perfil.html
  if (path.endsWith('/perfil.html')) {
    if (!token || (Date.now() - tokenTime > MAX_TIME)) {
      localStorage.removeItem('token');
      localStorage.removeItem('token_time');
      localStorage.removeItem('username');
      window.location.href = 'login.html';
      return false;
    }
    // 4) En perfil, montamos saludo bajo el logo en vez del nav estándar
    const nombreUsuarioElemento = document.getElementById('nombreUsuario1'); // Corregido el ID
    if (nombreUsuarioElemento) {
      nombreUsuarioElemento.innerText = localStorage.getItem('username') || '';
    }
  } else {
    // 5) En todas las demás páginas, si hay sesión válida, mostramos el nav estándar
    if (token && (Date.now() - tokenTime <= MAX_TIME)) {
      const nombreUsuario = localStorage.getItem('username');
      if (nombreUsuario) {
        mostrarUserNav({ nombreUsuario });
      }
    }
  }

  return true;
}

// FUNCIÓN PARA ACTUALIZAR NAV 
function mostrarUserNav(user) {
  document.getElementById('nav-login').style.display    = 'none'; //Oculto en el nav el botón de login y el de registro
  document.getElementById('nav-register').style.display = 'none';

  var ul = document.querySelector('nav ul'); //Busco el elemento ul dentro del nav para agregarle los elementos de usuario y logout

  // Hago el saludo
  var liUser = document.createElement('li'); //Creo dentro de ul, una etiqueta li
  liUser.textContent = `Hola, ${user.nombreUsuario}`; //Añado el texto diciendo Hola y el nombre de usuario
  ul.appendChild(liUser);//Lo añado al ul

  //Creo un li para que el usuario pueda ir al perfil (Sólo si está conectado)

  var liPerfil = document.createElement('li'); //Creo en el nav una etiqueta li
  var aRef = document.createElement('a');//Creo la etiqueta a
  aRef.href = 'perfil.html'; //Le añado el href para que redirija a la página de perfil
  aRef.textContent = 'Perfil'; //Le añado el texto de la etiqueta a
  liPerfil.appendChild(aRef); //Añado la etiqueta a al li
  ul.appendChild(liPerfil); //Añado el li al ul

  // Logout
  var liLogout = document.createElement('li'); //Creo otro li para el logout
  var btnLogout = document.createElement('button'); //Creo un botón dentro del li
  btnLogout.textContent = 'Logout';
  btnLogout.addEventListener('click', logout); //Le añado un evento al botón para que al hacer click llame a la función logout
  liLogout.appendChild(btnLogout); //Añado el botón al li
  ul.appendChild(liLogout);//Añado el li al ul
}

// FUNCIÓN LOGOUT
async function logout() { //Nuevamente utilizo una función async para poder usar await y manejar la promesa de fetch
  var token = localStorage.getItem('token'); //Obtengo el token guardado al inciar sesión
  try {
    await fetch('http://localhost:8001/api/logout', { //Hago la petición al servidor a la ruta logout para cerrar sesión
      method: 'POST', //Método POST
      headers: {
        'Authorization': 'Bearer ' + token, //Mando el token de autenticación en el encabezado de la petición
        'Accept':        'application/json' //Accept para recibir la respuesta en formato JSON
      }
    });
  } catch (err) { //Capturo el error si lo hubiese
    console.error('Error en logout:', err); //Lo muestro por consola
  }

  localStorage.removeItem('token'); //Elimino el token de localStorage
  localStorage.removeItem('token_time'); //Elimino el token_time de localStorage
  alert('¡Hasta luego!'); //Muestro un mensaje de éxito
  window.location.href = 'login.html'; //Redirijo a la página de login
}

async function obtenerDatosUsuario() {
  try {
    // Obtengo el token guardado al iniciar sesión
    var token = localStorage.getItem('token');
    if (!token) throw new Error('No hay token de sesión');

    // Guardamos la respuesta de la petición del perfil
    var res = await fetch('http://localhost:8001/api/profile', {
      headers: {
        'Authorization': 'Bearer ' + token, // Mandamos el token de autenticación
        'Accept':        'application/json'  // Recibir JSON
      }
    });
    if (!res.ok) throw new Error('Error al obtener perfil'); // Si falla, vamos al catch

    // Desestructuramos respuesta
    var { usuario, cliente, contrato } = await res.json();

    // Relleno los campos del usuario
    document.getElementById('nombreUsuario1').innerText = usuario.nombreUsuario;
    document.getElementById('nombre').value             = usuario.nombre;
    document.getElementById('email').value              = usuario.email;

    // 1) Si no existe cliente, alerta y NO return: queremos seguir y mostrar alerta contrato si toca
    const faltanDatosCliente = (
      !cliente ||
      !cliente.apellidos ||
      !cliente.tlf       ||
      !cliente.direccion ||
      !cliente.municipio ||
      !cliente.provincia ||
      !cliente.DNI
    );
    if (faltanDatosCliente) {
      mostrarAlertaCliente();
    } else {
      // si ya está completo, rellenamos el formulario de cliente
      document.getElementById('apellidos').value = cliente.apellidos;
      document.getElementById('tlf').value       = cliente.tlf;
      document.getElementById('direccion').value = cliente.direccion;
      document.getElementById('municipio').value = cliente.municipio;
      document.getElementById('provincia').value = cliente.provincia;
      document.getElementById('DNI').value       = cliente.DNI;
    }

        // 2) Si no hay contrato, mostramos su alerta
    if (!contrato) {
      mostrarAlertaContrato();
    } else {
      // si existe contrato, rellenamos sus datos
      document.getElementById('contrato_id').value                    = contrato.id;
      document.getElementById('numero_de_atenciones').value           = contrato.numero_de_atenciones;
      document.getElementById('numero_de_atenciones_realizadas').value = contrato.numero_de_atenciones_realizadas;
      document.getElementById('fecha_inicio').value                   = contrato.fecha_inicio.split(' ')[0];
      document.getElementById('fecha_fin').value                      = contrato.fecha_fin.split(' ')[0];
    }

    // 6) Verificar si cliente tiene datos vacíos para alerta
    if (!cliente.apellidos && !cliente.tlf && !cliente.direccion && !cliente.municipio && !cliente.provincia && !cliente.DNI) {
      mostrarAlertaCliente();
    }

  } catch (err) {
    console.error('Error al obtener perfil:', err);
    alert('No se pudieron cargar tus datos. Por favor, inténtalo de nuevo.');
  }
}

function mostrarAlertaCliente() {
  const alerta = document.getElementById('alerta-cliente');
  alerta.innerText = '⚠️ Para poder realizar citas debes rellenar tus datos de cliente.';
  alerta.style.display = 'block';
}

function mostrarAlertaContrato() {
  const alerta = document.getElementById('alerta-contrato');
  alerta.innerText = '⚠️ Para empezar un contrato de fidelidad, haz tu primera reserva!';
  alerta.style.display = 'block';
}

/**
 * Carga y muestra las últimas citas del cliente en la sección de perfil.
 * Determina si mostrar un botón para 'Evaluar' o 'Ver opinión' según si ya hay un comentario.
 * Si es true, las citas con estado 'cancelada' también se mostrarán. Por defecto es false.
 */
async function cargarUltimasCitasPerfil(mostrarCitasCanceladas = false) {
  const container = document.getElementById('listaCitas');
  container.innerHTML = ''; // Limpiar contenido previo

  try {
    const token = localStorage.getItem('token');
    if (!token) throw new Error('No autenticado. Por favor, inicia sesión.');

    // 1) Obtener el ID del cliente desde el endpoint de perfil
    const perfilRes = await fetch('http://localhost:8001/api/profile', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (!perfilRes.ok) throw new Error('Error al obtener perfil para cargar citas.');
    const perfilData = await perfilRes.json();
    const idCliente = perfilData.cliente?.id;
    if (!idCliente) {
      container.innerHTML = '<p>Completa tus datos de cliente para ver tus citas.</p>';
      return;
    }

    // 2) Obtener las citas del cliente
    const res = await fetch(`http://localhost:8001/api/customers/${idCliente}/appointments`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (!res.ok) {
      throw new Error(`El servidor devolvió un error inesperado: ${res.status}`);
    }
    const { data: citas, message } = await res.json();

    // Mostrar mensaje del backend
    container.innerHTML = `<p>${message}</p>`;

    // 3) Filtrar y pintar cada cita
    citas
      .filter(cita => mostrarCitasCanceladas || cita.estado.trim().toLowerCase() !== 'cancelada')
      .forEach(cita => {
        const tarjeta = document.createElement('div');
        tarjeta.className = 'cita-card';
        tarjeta.dataset.citaId = cita.id;

        const fechaObj = new Date(cita.fecha);
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
          day: '2-digit', month: '2-digit', year: 'numeric'
        });
        const horaFormateada = fechaObj.toLocaleTimeString('es-ES', {
          hour: '2-digit', minute: '2-digit', hour12: false
        });

        tarjeta.dataset.servicioNombre  = cita.servicios.map(s => s.nombre).join(', ');
        tarjeta.dataset.citaFecha        = fechaFormateada;
        tarjeta.dataset.citaHora         = horaFormateada;
        tarjeta.dataset.empleadoNombre   = cita.empleado
          ? `${cita.empleado.usuario.nombre} ${cita.empleado.apellidos}`.trim()
          : 'N/A';
        tarjeta.dataset.tieneOpinion     = cita.has_comment ? 'true' : 'false';

        // Contenido estático de la tarjeta
        tarjeta.innerHTML = `
          <p><strong>ID:</strong> ${cita.id}</p>
          <p><strong>Fecha:</strong> ${fechaFormateada} ${horaFormateada}</p>
          <p><strong>Estado:</strong> ${cita.estado}</p>
          <p><strong>Servicios:</strong> ${cita.servicios.map(s => s.nombre).join(', ')}</p>
        `;

        // Crear contenedor de acciones
        const botonDiv = document.createElement('div');
        botonDiv.className = 'cita-actions';

        // Según estado, crear botón con su listener
        const estado = cita.estado.trim().toLowerCase();
        let btn;
        if (estado === 'pendiente') {
          btn = document.createElement('button');
          btn.className = 'btn';
          btn.innerText = 'Cancelar';
          btn.addEventListener('click', () => cancelarCita(cita.id));
        } else if (estado === 'completada') {
          if (cita.has_comment) {
            btn = document.createElement('button');
            btn.className = 'btn';
            btn.innerText = 'Ver opinión';
            btn.addEventListener('click', mostrarOpinion);
          } else {
            btn = document.createElement('button');
            btn.className = 'btn';
            btn.innerText = 'Evaluar';
            btn.addEventListener('click', abrirModalEvaluar);
          }
        }

        if (btn) {
          botonDiv.appendChild(btn);
          tarjeta.appendChild(botonDiv);
          container.appendChild(tarjeta);
        }
      });

  } catch (error) {
    console.error('Error al cargar últimas citas:', error);
    container.innerHTML = '<p>Error al cargar el historial de citas. Inténtalo más tarde.</p>';
  }
}

// FUNCIÓN PARA CANCELAR UNA CITA
async function cancelarCita(citaId) {
    const token = localStorage.getItem('token');
    if (!token) {
        alert('No autenticado');
        return;
    }

    try {
        const res = await fetch(`http://localhost:8001/api/appointments/${citaId}`, {
            method: 'PUT',
            headers: {
                'Content-Type':  'application/json',
                'Authorization': 'Bearer ' + token,
                'Accept':        'application/json'
            },
            body: JSON.stringify({ estado: 'cancelada' })
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => null);
            console.error('Error al cancelar cita:', errData);
            alert('No se pudo cancelar la cita.');
            return;
        }

        alert(`Cita #${citaId} cancelada correctamente`);
        await cargarUltimasCitasPerfil();

    } catch (error) {
        console.error('Error en la petición para cancelar cita:', error);
        alert('Error de red o servidor al cancelar la cita.');
    }
}

/**
 * Abre el modal para evaluar una cita (mostrando el formulario).
 * Se obtiene el ID de la cita y otros detalles desde el dataset del card padre.
 */
async function abrirModalEvaluar(e) {
    const card = e.target.closest('.cita-card');
    const citaId = card.dataset.citaId;
    const servicioNombre = card.dataset.servicioNombre;
    const citaFecha      = card.dataset.citaFecha;
    const citaHora       = card.dataset.citaHora;
    const empleadoNombre = card.dataset.empleadoNombre;

    document.getElementById('modalComentario').style.display = 'flex';
    document.getElementById('modalComentarioTitle').innerText = `Cargando detalles de la cita...`;
    document.getElementById('formComentario').style.display = 'none';
    document.getElementById('contenedorVerOpinion').style.display = 'none';

    setTimeout(() => {
        try {
            document.getElementById('citaIdParaComentario').value = citaId;
            document.getElementById('textoComentario').value = '';
            document.getElementById('valoracionComentario').value = '';

            document.getElementById('modalComentarioTitle').innerText = `Evaluar: ${servicioNombre} el ${citaFecha} a las ${citaHora} con ${empleadoNombre}`;
            
            document.getElementById('formComentario').style.display = 'block';

        } catch (error) {
            console.error('Error al abrir modal de evaluación:', error);
            document.getElementById('modalComentarioTitle').innerText = `Error al cargar cita.`;
            document.getElementById('formComentario').style.display = 'none';
            document.getElementById('contenedorVerOpinion').style.display = 'none';
        }
    }, 50);
}

/**
 * Muestra la opinión existente (sin formulario de edición) para una cita.
 * Obtiene el ID de la cita y otros detalles desde el dataset del card padre.
 */
async function mostrarOpinion(e) {
  const card           = e.target.closest('.cita-card');
  const { citaId, servicioNombre, citaFecha, citaHora, empleadoNombre } = card.dataset;
  const modal          = document.getElementById('modalComentario');
  const title          = document.getElementById('modalComentarioTitle');
  const form           = document.getElementById('formComentario');
  const view           = document.getElementById('contenedorVerOpinion');
  const btnAct         = document.getElementById('btnActualizarOpinion');
  const btnDel         = document.getElementById('btnEliminarOpinion');
  const btnCancel      = document.getElementById('btnCancelarUpdate');
  const inputCita      = document.getElementById('citaIdParaComentario');

  // Rellenar datos
  inputCita.value     = citaId;
  title.innerText     = `Opinión sobre ${servicioNombre}`;
  document.getElementById('servicioVerOpinion').innerText   = servicioNombre;
  document.getElementById('fechaVerOpinion').innerText      = `${citaFecha} a las ${citaHora}`;
  document.getElementById('empleadoVerOpinion').innerText   = empleadoNombre;
  document.getElementById('textoVerOpinion').innerText      = 'Cargando…';
  document.getElementById('valoracionVerOpinion').innerText = '';

  // Mostrar solo lectura
  form.style.display      = 'none';
  view.style.display      = 'block';
  btnAct.style.display    = 'inline-block';
  btnDel.style.display    = 'inline-block';
  btnCancel.style.display = 'none';
  modal.style.display     = 'flex';

  // Conectar botones sin ejecutar
  btnAct.onclick    = onClickActualizarOpinion;
  btnDel.onclick    = onClickEliminarOpinion;
  btnCancel.onclick = onClickCancelarUpdate;

  // Cargar opinión
  try {
    const token = localStorage.getItem('token');
    const res = await fetch(
      `http://localhost:8001/api/appointments/${citaId}/comentarios`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept':        'application/json'
        }
      }
    );
    if (!res.ok) throw new Error();
    const data = await res.json();
    document.getElementById('textoVerOpinion').innerText      = data.texto      || '—';
    document.getElementById('valoracionVerOpinion').innerText = data.valoracion != null ? data.valoracion : '—';
  } catch {
    document.getElementById('textoVerOpinion').innerText      = 'Error al cargar';
    document.getElementById('valoracionVerOpinion').innerText = '';
  }
}


/* Envía el comentario (POST o PUT a /api/appointments/{citaId}/comentarios).
 * Intercepta el evento submit del formulario de comentario.
 */
async function enviarComentario(event) {
  event.preventDefault();
  const btnEnviar    = document.querySelector('#formComentario button[type="submit"]');
  const textoOriginal= btnEnviar.innerText;
  btnEnviar.disabled = true;
  btnEnviar.innerText= 'Evaluando…';

  try {
    const token = localStorage.getItem('token');
    if (!token) throw new Error('No autenticado.');

    const citaId = document.getElementById('citaIdParaComentario').value;
    const texto  = document.getElementById('textoComentario').value.trim();
    const valor  = document.getElementById('valoracionComentario').value;

    if (!texto || !valor) {
      alert('Debes escribir un comentario y seleccionar una valoración.');
      return;
    }

    const res = await fetch(
      `http://localhost:8001/api/appointments/${citaId}/comentarios`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type' : 'application/json',
          'Accept'       : 'application/json'
        },
        body: JSON.stringify({ texto, valoracion: valor })
    });

    if (!res.ok) {
      const err = await res.json().catch(() => null);
      throw new Error(err?.message || `Error al enviar comentario: ${res.status}`);
    }

    document.getElementById('modalComentario').style.display = 'none';
    await cargarUltimasCitasPerfil(false);

  } catch (error) {
    console.error('Error al enviar comentario:', error);
    alert(error.message || 'Hubo un problema al enviar tu opinión.');
  } finally {
    btnEnviar.disabled = false;
    btnEnviar.innerText= textoOriginal;
  }
}

/**
 * Cierra el modal de comentario.
 */
function cerrarModalComentario() {
    document.getElementById('modalComentario').style.display = 'none';
}

/**
 * Inicializa los eventos del modal de comentarios (cerrar y enviar formulario).
 */
function inicializarModalComentario() {
  document.getElementById('closeModalComentario').onclick = cerrarModalComentario;
  const form = document.getElementById('formComentario');
  form.addEventListener('submit', enviarComentario);
  window.addEventListener('click', e => {
    if (e.target.id === 'modalComentario') cerrarModalComentario();
  });
}

// --------------------------------------------------
// CARGAR COMENTARIOS RECIENTES (para pagina3.html)
// --------------------------------------------------
// 2) Cargar opiniones recientes en pagina3.html mostrando servicio y empleado
async function cargarComentariosRecientes() {
  const cont = document.getElementById('comentariosRecientes');
  cont.innerHTML = ''; // limpiar contenido previo

  try {
    const res = await fetch('http://localhost:8001/api/comentarios/recientes', {
      headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error(`Error (${res.status}) al cargar comentarios recientes`);

    const comentarios = await res.json();

    comentarios.forEach(c => {
      // Nombre del cliente
      const nombreCliente = `${c.cliente.usuario.nombre} ${c.cliente.apellidos}`.trim();

      // Servicios de la cita
      const servicios = Array.isArray(c.cita.servicios) && c.cita.servicios.length
        ? c.cita.servicios.map(s => s.nombre).join(', ')
        : '—';

      // Empleado de la cita
      const empleado = c.cita.empleado && c.cita.empleado.usuario
        ? `${c.cita.empleado.usuario.nombre} ${c.cita.empleado.apellidos}`.trim()
        : '—';

      // Fecha formateada
      const fecha = new Date(c.created_at)
        .toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });

      // Valoración
      const valoracion = c.valoracion != null ? c.valoracion : '—';

      // Construir el HTML del comentario
      const div = document.createElement('div');
      div.className = 'comentario';
      div.innerHTML = `
        <p class="comentario-texto">"${c.texto}"</p>
        <p class="comentario-meta">
          <strong>Cliente:</strong> ${nombreCliente}<br>
          <strong>Servicio(s):</strong> ${servicios}<br>
          <strong>Empleado:</strong> ${empleado}<br>
          <strong>Fecha:</strong> ${fecha}<br>
          <strong>Valoración:</strong> ${valoracion} / 5
      `;
      cont.appendChild(div);
    });

  } catch (error) {
    console.error('Error al cargar comentarios recientes:', error);
    cont.innerHTML = '<p>No se pudieron cargar los comentarios.</p>';
  }
}

// --------------------------------------------------
// CARGAR SERVICIOS (para pagina3.html)
// --------------------------------------------------
async function cargarEquipo() {
  var cont = document.getElementById('equipoContainer');
  if (!cont) return;

  try {
    const res = await fetch('http://localhost:8001/api/employees', {
      headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Error al obtener empleados');
    const empleados = await res.json();

    cont.innerHTML = '';
    if (empleados.length === 0) {
      cont.innerHTML = '<p>No hay miembros del equipo para mostrar.</p>';
      return;
    }

    empleados.forEach(emp => {
      const nombreCompleto = `${emp.usuario.nombre} ${emp.apellidos}`;
      const tarjeta = document.createElement('div');
      tarjeta.className = 'team-member';
      tarjeta.innerHTML = `
        <img src="${emp.photoUrl||'assets/imges/equipo/placeholder.png'}" alt="${nombreCompleto}">
        <br>
        <br>
        <h3>${nombreCompleto}</h3>
        <br>
        <br>
        <button class="btn">Más información</button>
      `;
      tarjeta.querySelector('button').addEventListener('click', () => {
        if (typeof mostrarEmpleadoModal === 'function') {
            mostrarEmpleadoModal(emp); 
        } else {
            console.warn('`mostrarEmpleadoModal` no está definida.');
        }
      });
      cont.appendChild(tarjeta);
    });

  } catch (err) {
    console.error('cargarEquipo():', err);
    document.getElementById('equipoContainer').innerHTML =
      '<p>No se pudo cargar el equipo.</p>';
  }
}

/**
 * Obtiene la lista de servicios y los pinta en el contenedor #servicesContainer.
 * Cada tarjeta de servicio recibe atributos data-* con la lista de empleados asignados (JSON).
 */
async function cargarServicios() {
  try {
    const respuesta = await fetch('http://localhost:8001/api/services', {
      headers: { 'Accept': 'application/json' }
    });
    const servicios = await respuesta.json();
    const contenedorServicios = document.getElementById('servicesContainer');
    contenedorServicios.innerHTML = '';

    servicios.forEach(servicio => {
      const tarjeta = document.createElement('div');
      tarjeta.className = 'service';
      tarjeta.dataset.serviceId       = servicio.id;
      tarjeta.dataset.especialidadId  = servicio.especialidad_id;
      tarjeta.dataset.nombreServicio  = servicio.nombre;
      tarjeta.dataset.duracionServicio = servicio.duracion;
      tarjeta.dataset.precioServicio   = servicio.precio;

      let listaEmpleados = [];
      if (Array.isArray(servicio.empleados) && servicio.empleados.length > 0) {
        listaEmpleados = servicio.empleados.map(e => ({
          id:   e.id,
          name: `${e.usuario ? e.usuario.nombre : ''} ${e.apellidos}`.trim()
        }));
      }
      tarjeta.dataset.empleadosAsignados = JSON.stringify(listaEmpleados);

      tarjeta.innerHTML = `
        <h3>${servicio.nombre}</h3>
        <p>${servicio.precio}€</p>
        <button class="btn reservar-btn">Reservar</button>
      `;
      contenedorServicios.appendChild(tarjeta);
    });

    if (typeof activarBotonesReservar === 'function') {
        activarBotonesReservar();
    } else {
        console.warn('`activarBotonesReservar` no está definida. Los botones de reserva no funcionarán.');
    }
  } catch (error) {
    console.error('Error al cargar servicios', error);
    alert('No se pudieron cargar los servicios.');
  }
}

function gestionarFormularioPerfil() {
  const editBtn     = document.getElementById('editBtn');
  const saveBtn     = document.getElementById('saveBtn');
  const cancelBtn   = document.getElementById('cancelBtn');
  const rellenarBtn = document.getElementById('rellenarBtn');
  const inputs      = document.querySelectorAll('#editable input');

  const toggleEditMode = (isEditing) => {
    inputs.forEach(i => {
      if (i.name !== 'nombre' && i.name !== 'email') {
        i.readOnly = !isEditing;
      }
    });
    editBtn    && (editBtn.style.display    = isEditing ? 'none' : 'inline-block');
    rellenarBtn&& (rellenarBtn.style.display= isEditing ? 'none' : 'inline-block');
    saveBtn    && (saveBtn.style.display    = isEditing ? 'inline-block' : 'none');
    cancelBtn  && (cancelBtn.style.display  = isEditing ? 'inline-block' : 'none');
  };

  editBtn?.addEventListener('click', () => toggleEditMode(true));
  cancelBtn?.addEventListener('click', () => location.reload());
  saveBtn?.addEventListener('click', async e => {
    e.preventDefault();
    const payload = {};
    inputs.forEach(i => payload[i.name] = i.value);
    try {
      const res = await fetch('http://localhost:8001/api/profile', {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type' : 'application/json',
          'Accept'       : 'application/json'
        },
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.message || 'Error del servidor');
      }
      alert('¡Perfil guardado correctamente!');
      location.reload();
    } catch (err) {
      console.error('Error guardando perfil:', err);
      alert('No se pudo guardar tu perfil. ' + err.message);
    }  
  });
  rellenarBtn?.addEventListener('click', async () => {
    try {
      const res = await fetch('http://localhost:8001/api/profile', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        }
      });
      if (!res.ok) throw new Error();
      const { usuario, cliente } = await res.json();
      inputs.forEach(i => {
        i.value = ({
          nombreUsuario: usuario.nombreUsuario,
          nombre       : usuario.nombre,
          email        : usuario.email,
          apellidos    : cliente.apellidos,
          tlf          : cliente.tlf,
          direccion    : cliente.direccion,
          municipio    : cliente.municipio,
          provincia    : cliente.provincia,
          DNI          : cliente.DNI
        })[i.id] || '';
      });
    } catch (err) {
      console.warn('No se pudo rellenar automáticamente', err);
    }
  });
}

// FUNCIONES PARA ACTUALIZAR/ELIMINAR OPINIÓN

function onClickActualizarOpinion() {
  const form      = document.getElementById('formComentario');
  const view      = document.getElementById('contenedorVerOpinion');
  const btnAct    = document.getElementById('btnActualizarOpinion');
  const btnDel    = document.getElementById('btnEliminarOpinion');
  const btnCancel = document.getElementById('btnCancelarUpdate');
  const submit   = form.querySelector('button[type="submit"]');

  // mostrar formulario de edición
  view.style.display      = 'none';
  btnAct.style.display    = 'none';
  btnDel.style.display    = 'none';
  btnCancel.style.display = 'inline-block';
  form.style.display      = 'block';

  // configuración del botón
  submit.innerText        = 'Actualizar opinión';
  submit.disabled         = false;
  submit.classList.remove('btn--disabled');

  // cambiar handler a PUT
  form.removeEventListener('submit', enviarComentario);
  form.addEventListener('submit', actualizarComentario);
}

function onClickCancelarUpdate() {
  const form    = document.getElementById('formComentario');
  const view    = document.getElementById('contenedorVerOpinion');
  const btnAct  = document.getElementById('btnActualizarOpinion');
  const btnDel  = document.getElementById('btnEliminarOpinion');
  const btnCancel = document.getElementById('btnCancelarUpdate');

  // revertir a solo lectura
  form.style.display      = 'none';
  view.style.display      = 'block';
  btnAct.style.display    = 'inline-block';
  btnDel.style.display    = 'inline-block';
  btnCancel.style.display = 'none';

  // restaurar handler original (POST)
  form.removeEventListener('submit', actualizarComentario);
  form.addEventListener('submit', enviarComentario);
}


// 2) PUT de actualización
async function actualizarComentario(event) {
  event.preventDefault();
  const form     = event.target;
  const submit   = form.querySelector('button[type="submit"]');
  const citaId   = document.getElementById('citaIdParaComentario').value;
  const texto    = document.getElementById('textoComentario').value.trim();
  const valor    = document.getElementById('valoracionComentario').value;
  const token    = localStorage.getItem('token');
  const modal    = document.getElementById('modalComentario');

  // Feedback visual
  submit.innerText = 'Actualizando…';
  submit.disabled  = true;
  submit.classList.add('btn--disabled');

  try {
    const res = await fetch(
      `http://localhost:8001/api/appointments/${citaId}/comentarios`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type':  'application/json',
          'Accept':        'application/json'
        },
        body: JSON.stringify({ texto, valoracion: valor })
      }
    );
    if (!res.ok) throw new Error();

    // Cerrar modal y recargar citas
    modal.style.display = 'none';
    await cargarUltimasCitasPerfil(false);

  } catch (err) {
    console.error('actualizarComentario:', err);
    alert('No se pudo actualizar la opinión.');
    submit.innerText = 'Actualizar opinión';
    submit.disabled  = false;
    submit.classList.remove('btn--disabled');
  }
}

// 1) Al hacer click en “Eliminar opinión”…
async function onClickEliminarOpinion() {
  if (!confirm('¿Seguro que quieres eliminar tu opinión?')) return;

  const btnDel     = document.getElementById('btnEliminarOpinion');
  const textoOrig  = btnDel.innerText;
  btnDel.disabled  = true;
  btnDel.innerText = 'Eliminando…';

  const citaId = document.getElementById('citaIdParaComentario').value;
  const token  = localStorage.getItem('token');

  try {
    const res = await fetch(
      `http://localhost:8001/api/appointments/${citaId}/comentarios`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept':        'application/json'
        }
      }
    );
    if (!res.ok) throw new Error();

    document.getElementById('modalComentario').style.display = 'none';
    await cargarUltimasCitasPerfil(false);

  } catch (err) {
    console.error('onClickEliminarOpinion:', err);
    alert('No se pudo eliminar la opinión.');
    btnDel.disabled  = false;
    btnDel.innerText = textoOrig;
  }
}


//Variable controladora para usar el loader o no
const USAR_LOADER = true;

document.addEventListener('DOMContentLoaded', async () => {
  const loader    = document.getElementById('loader-container');
  const contenido = document.getElementById('contenido');

  if (!loader || !contenido) return;

  if (USAR_LOADER) {
    loader.style.display    = 'block';
    contenido.style.display = 'none';

    try {
      try {
        const resp = await fetch('loader.html');
        if (resp.ok) {
          loader.innerHTML = await resp.text();
        } else {
          console.warn('No se pudo cargar loader.html:', resp.status);
        }
      } catch (ignore) {}

      await initAuth();

      if (window.location.pathname.endsWith('/perfil.html')) {
        try {
          await obtenerDatosUsuario();
          await cargarUltimasCitasPerfil(false);
          inicializarModalComentario();
          gestionarFormularioPerfil();
        } catch (err) {
          console.error('Error al inicializar perfil:', err);
        }
      }

      document.getElementById('form-login')?.addEventListener('submit', login);
      document.getElementById('form-register')?.addEventListener('submit', register);

      if (window.location.pathname.endsWith('/pagina3.html')) {
        await cargarComentariosRecientes();
        await cargarServicios();
      }

      if (window.location.pathname.endsWith('/pagina2.html')) {
        await cargarEquipo();
      }

    } catch (err) {
      console.error('Error en init:', err);
    } finally {
      loader.style.display    = 'none';
      contenido.style.display = 'block';
    }

  } else {
    contenido.style.display = 'block';
    loader.style.display    = 'none';

    try {
      await initAuth();

      if (window.location.pathname.endsWith('/perfil.html')) {
        try {
          await obtenerDatosUsuario();
          await cargarUltimasCitasPerfil(false);
          inicializarModalComentario();
          gestionarFormularioPerfil();
        } catch (err) {
          console.error('Error al inicializar perfil:', err);
        }
      }

      document.getElementById('form-login')?.addEventListener('submit', login);
      document.getElementById('form-register')?.addEventListener('submit', register);

      if (window.location.pathname.endsWith('/pagina3.html')) {
        await cargarComentariosRecientes();
        await cargarServicios();
      }

      if (window.location.pathname.endsWith('/pagina2.html')) {
        await cargarEquipo();
      }

    } catch (err) {
      console.error('Error en init:', err);
    }
  }
});
