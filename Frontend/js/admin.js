const API_URL = 'http://localhost:8001/api';
const TOKEN = localStorage.getItem('token');

// Caché global de datos
let cacheDeDatos = {
  usuarios: [],
  clientes: [],
  trabajadores: [],
  especialidades: [],
  servicios: [],
  empleadoEspecialidades: [],
};

function setupEventListeners() {
  document.querySelectorAll('nav a[data-seccion]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      mostrarSeccion(e.target.dataset.seccion);
    });
  });
  document.getElementById('logout-btn')?.addEventListener('click', logout);
  document.querySelector('.modal .close-btn')?.addEventListener('click', cerrarModal);
  // Botones de añadir
  document.querySelectorAll('button[data-entity]').forEach(btn => {
    btn.addEventListener('click', () => abrirModalEdicion(btn.dataset.entity));
  });
}

// Util para fetch seguro que devuelve [] en 404/204 o error de red
async function safeFetchArray(url, headers) {
  try {
    const res = await fetch(url, { method:'GET', headers });
    if (res.ok) {
      return await res.json();
    } else if (res.status === 404 || res.status === 204) {
      return [];
    } else {
      console.error(`Error ${res.status} cargando ${url}`);
      return [];
    }
  } catch (err) {
    console.error(`Error de red en ${url}:`, err);
    return [];
  }
}

async function cargarDatos() {
  const headers = {
    'Authorization': `Bearer ${TOKEN}`,
    'Accept':        'application/json'
  };

  // Todas las colecciones en paralelo, cada una con safeFetchArray
  const [
    usuarios,
    clientes,
    trabajadores,
    especialidades,
    servicios,
    citas,
    comentarios
  ] = await Promise.all([
    safeFetchArray(`${API_URL}/usuarios`, headers),
    safeFetchArray(`${API_URL}/customers`, headers),
    safeFetchArray(`${API_URL}/employees`, headers),
    safeFetchArray(`${API_URL}/specialties`, headers),
    safeFetchArray(`${API_URL}/services`, headers),
    safeFetchArray(`${API_URL}/appointments`, headers),
    safeFetchArray(`${API_URL}/comentarios/recientes`, headers),
  ]);

  // Asignamos al caché
  cacheDeDatos.usuarios       = usuarios;
  cacheDeDatos.clientes      = clientes;
  cacheDeDatos.trabajadores  = trabajadores;
  cacheDeDatos.especialidades= especialidades;
  cacheDeDatos.servicios     = servicios;
  cacheDeDatos.citas         = citas;
  cacheDeDatos.comentarios   = comentarios;
}

function mostrarSeccion(idSeccion) {
  document.querySelectorAll('.seccion').forEach(sec => sec.classList.remove('activa'));
  const sec = document.getElementById(`seccion-${idSeccion}`);
  if (!sec) return;
  sec.classList.add('activa');
  document.getElementById('admin-title').innerText =
    `Panel de Administración - ${idSeccion.charAt(0).toUpperCase()+idSeccion.slice(1)}`;

  switch(idSeccion) {
    case 'clientes':
      renderClientes(); break;
    case 'trabajadores':
      renderTrabajadores();
      renderEspecialidades();
      break;
    case 'servicios':
      renderServicios(); break;
    case 'citas':
      renderCitas(); break;
    case 'comentarios':
      renderComentarios(); break;
  }
}

function renderClientes() {
  const { usuarios, clientes, trabajadores } = cacheDeDatos;
  const body = document.getElementById('tabla-clientes-body');
  // excluir trabajadores
  const trabIDs = new Set(cacheDeDatos.trabajadores.map(t => t.usuario_id || t.id));
  const users = usuarios.filter(u => !trabIDs.has(u.id));
  body.innerHTML = users.length ? users.map(u => {
    const c = clientes.find(x => x.email === u.email);
    return `
      <tr>
        <td>${u.id}</td>
        <td>${u.nombreUsuario}</td>
        <td>${u.nombre}</td>
        <td>${u.email}</td>
        <td>${c?.tlf || 'No Registrado'}</td>
        <td>${c?.DNI || 'No Registrado'}</td>
        <td>${c?.direccion.replace(/\n/g,'<br>')||'No Registrado'}</td>
        <td>${c?.municipio||'No Registrado'}</td>
        <td>${c?.provincia||'No Registrado'}</td>
        <td>${c?.id||'No Registrado'}</td>
        <td class="acciones">
          <button data-action="edit" data-entity="cliente" data-id="${u.id}" class="btn-editar">Editar</button>
          <button data-action="delete" data-entity="cliente" data-id="${u.id}" class="btn-eliminar">Eliminar</button>
        </td>
      </tr>`;
  }).join('') : '<tr><td colspan="10">No hay usuarios/clientes.</td></tr>';
  delegateTableActions(body);
}

function renderTrabajadores() {
  const { trabajadores } = cacheDeDatos;
  const body = document.getElementById('tabla-trabajadores-body');
  body.innerHTML = trabajadores.length
    ? trabajadores.map(t => {
        const usuario = t.usuario; // viene eager-loaded
        const ids = (t.especialidades||[])
          .map(e => e.id).join(', ');
        return `
          <tr>
            <td>${t.id}</td>
            <td>${usuario.nombreUsuario}</td>
            <td>${usuario.nombre}</td>
            <td>${usuario.email}</td>
            <td>${ids || 'Sin Especialidad'}</td>
            <td class="acciones">
              <button data-action="edit" data-entity="trabajador" data-id="${t.id}" class="btn-editar">Editar</button>
              <button data-action="delete" data-entity="trabajador" data-id="${t.id}" class="btn-eliminar">Eliminar</button>
            </td>
          </tr>
        `;
      }).join('')
    : '<tr><td colspan="6">No hay trabajadores.</td></tr>';
  delegateTableActions(body);
}

function renderEspecialidades() {
  const { especialidades } = cacheDeDatos;
  const body = document.getElementById('tabla-especialidades-body');
  body.innerHTML = especialidades.map(e => `
    <tr>
      <td>${e.id}</td>
      <td>${e.nombre}</td>
      <td>
        <button data-action="edit" data-entity="especialidad" data-id="${e.id}" class="btn-editar">Editar</button>
        <button data-action="delete" data-entity="especialidad" data-id="${e.id}" class="btn-eliminar">Eliminar</button>
      </td>
    </tr>`).join('');
  delegateTableActions(body);
}

function renderServicios() {
  const { servicios } = cacheDeDatos;
  const body = document.getElementById('tabla-servicios-body');
  body.innerHTML = servicios.map(s => `
    <tr>
      <td>${s.id}</td>
      <td>${s.nombre}</td>
      <td>${s.precio}</td>
      <td>${s.duracion}</td>
      <td>
        <button data-action="edit" data-entity="servicio" data-id="${s.id}" class="btn-editar">Editar</button>
        <button data-action="delete" data-entity="servicio" data-id="${s.id}" class="btn-eliminar">Eliminar</button>
      </td>
    </tr>`).join('');
  delegateTableActions(body);
}

function renderCitas() {
  const { citas } = cacheDeDatos;
  const body = document.getElementById('tabla-citas-body');
  if (!citas.length) {
    body.innerHTML = `
      <tr>
        <td colspan="6" class="empty">No hay citas registradas.</td>
      </tr>`;
    return;
  }
  body.innerHTML = citas.map(a => `
    <tr>
      <td>${a.id}</td>
      <td>${a.customer_id||a.cliente_id}</td>
      <td>${a.employee_id||a.trabajador_id}</td>
      <td>${a.fecha||a.date||a.created_at}</td>
      <td>${a.hora||''}</td>
      <td class="acciones">
        <button
          class="btn-eliminar"
          onclick="eliminarCita(${a.id})">
          Eliminar
        </button>
      </td>
    </tr>
  `).join('');
}

function renderComentarios() {
  const { comentarios } = cacheDeDatos;
  const body = document.getElementById('tabla-comentarios-body');
  if (!comentarios.length) {
    body.innerHTML = `
      <tr>
        <td colspan="5" class="empty">No hay comentarios.</td>
      </tr>`;
    return;
  }
  body.innerHTML = comentarios.map(c => `
    <tr>
      <td>${c.id}</td>
      <td>${c.appointment_id||c.cita_id}</td>
      <td>${c.texto||c.comentario||c.contenido}</td>
      <td>${c.created_at}</td>
      <td class="acciones">
        <button
          class="btn-eliminar"
          onclick="eliminarComentario(${c.appointment_id||c.cita_id})">
          Eliminar
        </button>
      </td>
    </tr>
  `).join('');
}

// Funciones de eliminación específicas
async function eliminarCita(id) {
  if (!confirm(`¿Eliminar cita #${id}?`)) return;
  await fetch(`${API_URL}/appointments/${id}`, {
    method:'DELETE',
    headers:{ 'Authorization':`Bearer ${TOKEN}` }
  });
  await cargarDatos();
  mostrarSeccion('citas');
}

async function eliminarComentario(citaId) {
  if (!confirm(`¿Eliminar comentario de la cita #${citaId}?`)) return;
  await fetch(`${API_URL}/appointments/${citaId}/comentarios`, {
    method:'DELETE',
    headers:{ 'Authorization':`Bearer ${TOKEN}` }
  });
  await cargarDatos();
  mostrarSeccion('comentarios');
}



function delegateTableActions(tbody) {
  tbody.querySelectorAll('button').forEach(btn => {
    const { action, entity, id } = btn.dataset;
    if (action==='edit')   btn.onclick = () => abrirModalEdicion(entity, id);
    if (action==='delete') btn.onclick = () => eliminarEntidad(entity, id);
  });
}

// Modal genérico
function abrirModalEdicion(entity, id=null) {
  const modal = document.getElementById('edit-modal');
  const titleElem = document.getElementById('modal-title');
  const form = document.getElementById('edit-form');
  const isNew = !id;
  let data = {};
  if (!isNew) {
    // cargar datos existentes
    if (entity==='cliente') {
      const user = cacheDeDatos.usuarios.find(u=>u.id==id);
      const cli = cacheDeDatos.clientes.find(c=>c.email===user.email);
      data = { ...user, ...cli };
    } else data = cacheDeDatos[entity+'s'].find(x=>x.id==id);
  }
  const labels = { cliente:['Nuevo Cliente','Editar Cliente'], trabajador:['Nuevo Trabajador','Editar Trabajador'], especialidad:['Nueva Especialidad','Editar Especialidad'], servicio:['Nuevo Servicio','Editar Servicio']};
  titleElem.innerText = isNew?labels[entity][0]:`${labels[entity][1]} #${id}`;
  // generar campos
  let html = `<input type="hidden" name="id" value="${data.id||''}">`;
  switch(entity) {
    case 'cliente':
      html+=`
        <input type="hidden" name="usuario_id" value="${data.id||''}">
        <input type="hidden" name="cliente_id" value="${data.cliente_id||data.id||''}">
        <fieldset><legend>Usuario</legend>
          <label>Nombre:</label><input name="nombre" value="${data.nombre||''}" required>
          <label>Usuario:</label><input name="nombreUsuario" value="${data.nombreUsuario||''}" required>
          <label>Email:</label><input type="email" name="email" value="${data.email||''}" required>
        </fieldset>
        <fieldset><legend>Cliente</legend>
          <label>Apellidos:</label><input name="apellidos" value="${data.apellidos||''}">
          <label>Teléfono:</label><input name="tlf" value="${data.tlf||''}">
          <label>DNI:</label><input name="DNI" value="${data.DNI||''}">
          <label>Dirección:</label><input name="direccion" value="${data.direccion||''}">
          <label>Municipio:</label><input name="municipio" value="${data.municipio||''}">
          <label>Provincia:</label><input name="provincia" value="${data.provincia||''}">
        </fieldset>`;
      break;
    case 'trabajador':
      html+=`
        <label>Usuario:</label><input name="nombreUsuario" value="${data.nombreUsuario||''}" required>
        <label>Nombre:</label><input name="nombre" value="${data.nombre||''}" required>
        <label>Email:</label><input type="email" name="email" value="${data.email||''}" required>
      `;
      break;
    case 'especialidad':
      html+=`<label>Nombre:</label><input name="nombre" value="${data.nombre||''}" required>`;
      break;
    case 'servicio':
      html+=`
        <label>Nombre:</label><input name="nombre" value="${data.nombre||''}" required>
        <label>Precio:</label><input name="precio" type="number" value="${data.precio||''}" step="0.01" required>
        <label>Duración:</label><input name="duracion" type="number" value="${data.duracion||''}" required>
      `;
      break;
  }
  html+=`<div class="form-actions"><button type="submit" class="btn-guardar">Guardar</button></div>`;
  form.innerHTML = html;
  form.onsubmit = async e => {
    e.preventDefault();
    const payload = Object.fromEntries(new FormData(form).entries());
    await guardarCambiosEntidad(entity, payload);
    cerrarModal();
    await cargarDatos();
    mostrarSeccion(entity==='cliente'?'clientes':entity+'s');
  };
  modal.style.display='block';
}

function cerrarModal() {
  document.getElementById('edit-modal').style.display='none';
}

async function guardarCambiosEntidad(entity, data) {
  const id = data.id;
  const url = id?`${API_URL}/${entity}s/${id}`:`${API_URL}/${entity}s}`; // ajusta rutas si necesarias
  const method = id?'PUT':'POST';
  await fetch(url, { method, headers:{'Authorization':`Bearer ${TOKEN}`,'Content-Type':'application/json'}, body:JSON.stringify(data) });
}

async function eliminarEntidad(entity, id) {
  if(!confirm('¿Eliminar?')) return;
  await fetch(`${API_URL}/${entity}s/${id}`, { method:'DELETE', headers:{'Authorization':`Bearer ${TOKEN}`} });
  await cargarDatos();
  mostrarSeccion(entity==='cliente'?'clientes':entity+'s');
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

// Inicialización
document.addEventListener('DOMContentLoaded', async () => {
  if (!TOKEN) {
    alert('Login requerido');
    window.location.href = 'login.html';
    return;
  }
  setupEventListeners();
  await cargarDatos();
  mostrarSeccion('clientes');
});
