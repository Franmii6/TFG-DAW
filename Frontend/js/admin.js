// ───────────────────────────────────────────────────────────
// admin.js – Panel de Administración completo (CRUD por sección)
// ───────────────────────────────────────────────────────────

// ───────────────────────────────────────────────────────────
// 0) Configuración global de la API y autenticación
// ───────────────────────────────────────────────────────────
const API_BASE = 'http://localhost:8001/api';                    // URL base de tu API
const TOKEN    = localStorage.getItem('token');                  // JWT del usuario admin
// Headers comunes para todas las peticiones
const HEADERS   = {
  'Authorization': `Bearer ${TOKEN}`,
  'Content-Type':  'application/json',
  'Accept':        'application/json'
};

// ───────────────────────────────────────────────────────────
// 1) Variables para almacenar datos cargados
// ───────────────────────────────────────────────────────────
let usuarios     = [];    // array de /usuarios
let clientes     = [];    // array de /customers
let empleados    = [];    // array de /employees
let especialidades = [];  // array de /specialties
let servicios    = [];    // array de /services
let citas        = [];    // array de /appointments
let comentarios  = [];    // array de /comentarios/recientes

// ───────────────────────────────────────────────────────────
// 2) Al cargar la página: verifica token, carga datos, setea UI
// ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  if (!TOKEN) {
    alert('Debes iniciar sesión como admin.');
    return window.location.href = 'login.html';
  }
  await cargarTodosLosDatos();  // Trae usuarios, clientes, empleados, etc.
  inicializarUI();              // Botones, menú, modal
  mostrarSeccion('clientes');   // Sección inicial
});

// ───────────────────────────────────────────────────────────
// 3) Cargar datos de todas las secciones en paralelo
// ───────────────────────────────────────────────────────────
async function cargarTodosLosDatos() {
  const options = { headers: HEADERS };
  const rutas = [
    fetch(`${API_BASE}/usuarios`, options).then(r => r.json()).then(d => usuarios = d),
    fetch(`${API_BASE}/customers`, options).then(r => r.json()).then(d => clientes = d),
    fetch(`${API_BASE}/employees`, options).then(r => r.json()).then(d => empleados = d),
    fetch(`${API_BASE}/specialties`, options).then(r => r.json()).then(d => especialidades = d),
    fetch(`${API_BASE}/services`, options).then(r => r.json()).then(d => servicios = d),
    fetch(`${API_BASE}/appointments`, options).then(r => r.json()).then(d => citas = d),
    fetch(`${API_BASE}/comentarios/recientes`, options).then(r => r.json()).then(d => comentarios = d),
  ];
  await Promise.all(rutas);
}

// ───────────────────────────────────────────────────────────
// 4) Inicializar la UI: menú, botones “Añadir” y modal genérico
// ───────────────────────────────────────────────────────────
function inicializarUI() {
  // a) Menú de secciones
  document.querySelectorAll('nav a[data-seccion]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      mostrarSeccion(a.dataset.seccion);
    });
  });
  // b) Botones “Añadir”
  document.getElementById('add-trabajador-btn')
    .addEventListener('click', () => abrirModalTrabajador());
  document.getElementById('add-especialidad-btn')
    .addEventListener('click', () => abrirModalEspecialidad());
  document.getElementById('add-servicio-btn')
    .addEventListener('click', () => abrirModalServicio());
  // c) Cerrar modal al clicar fuera o en “X”
  const modal = document.getElementById('edit-modal');
  modal.addEventListener('click', e => {
    if (e.target.id === 'edit-modal') cerrarModal();
  });
  document.querySelector('.modal .close-btn')
    .addEventListener('click', cerrarModal);
}

// ───────────────────────────────────────────────────────────
// 5) Mostrar u ocultar secciones y renderizar su tabla
// ───────────────────────────────────────────────────────────
function mostrarSeccion(nombre) {
  document.querySelectorAll('.seccion').forEach(s => s.classList.remove('activa'));
  document.getElementById(`seccion-${nombre}`).classList.add('activa');
  document.getElementById('admin-title').innerText = {
    clientes:     'Gestión de Usuarios y Clientes',
    trabajadores: 'Gestión de Trabajadores y Especialidades',
    servicios:    'Gestión de Servicios',
    citas:        'Gestión de Citas',
    comentarios:  'Gestión de Comentarios'
  }[nombre] || '';
  // Render de la sección activa:
  switch (nombre) {
    case 'clientes':     renderTablaClientes();      break;
    case 'trabajadores': renderTablaTrabajadores();  renderTablaEspecialidades(); break;
    case 'servicios':    renderTablaServicios();     break;
    case 'citas':        renderTablaCitas();         break;
    case 'comentarios':  renderTablaComentarios();   break;
  }
}

// ───────────────────────────────────────────────────────────
// 6) Renders de tablas (con botones inline que llaman a funciones)
// ───────────────────────────────────────────────────────────
function renderTablaClientes() {
  const body = document.getElementById('tabla-clientes-body');
  // Filtrar usuarios que NO sean empleados
  const empIDs = new Set(empleados.map(e => e.usuario_id || e.id));
  const filtrados = usuarios.filter(u => !empIDs.has(u.id));

  if (!filtrados.length) {
    body.innerHTML = `<tr><td colspan="12" class="empty">No hay usuarios/clientes.</td></tr>`;
    return;
  }

  body.innerHTML = filtrados.map(u => {
    // Busca el registro de cliente relacionado
    const c = clientes.find(c => c.email === u.email) || {};
    return `
      <tr>
        <td>${u.id}</td>
        <td>${u.nombreUsuario}</td>
        <td>${u.nombre}</td>
        <!-- APÉNDICE: apellidos vienen de 'clientes', no de 'usuarios' -->
        <td>${c.apellidos  || '-'}</td>
        <td>${u.email}</td>
        <td>${c.tlf       || '-'}</td>
        <td>${c.DNI       || '-'}</td>
        <td>${c.direccion || '-'}</td>
        <td>${c.municipio || '-'}</td>
        <td>${c.provincia || '-'}</td>
        <td>${c.id        || '-'}</td>
        <td class="acciones">
          <button onclick="abrirModalCliente(${u.id})" class="btn-editar">
            Editar
          </button>
          <button onclick="eliminarCliente(${u.id})" class="btn-eliminar">
            Eliminar
          </button>
        </td>
      </tr>`;
  }).join('');
}


function renderTablaTrabajadores() {
  const body = document.getElementById('tabla-trabajadores-body');
  if (!empleados.length) {
    body.innerHTML = `<tr><td colspan="6" class="empty">No hay trabajadores.</td></tr>`;
    return;
  }
  body.innerHTML = empleados.map(t => {
    const u = usuarios.find(u => u.id === t.usuario_id) || {};
    const ids = (t.especialidades||[]).map(e=>e.id).join(',');
    return `
      <tr>
        <td>${t.id}</td>
        <td>${u.nombreUsuario}</td>
        <td>${u.nombre}</td>
        <td>${u.email}</td>
        <td>${ids || '-'}</td>
        <td class="acciones">
          <button onclick="abrirModalTrabajador(${t.id})" class="btn-editar">Editar</button>
          <button onclick="eliminarTrabajador(${t.id})"  class="btn-eliminar">Eliminar</button>
        </td>
      </tr>`;
  }).join('');
}

function renderTablaEspecialidades() {
  const body = document.getElementById('tabla-especialidades-body');
  if (!especialidades.length) {
    body.innerHTML = `<tr><td colspan="3" class="empty">No hay especialidades.</td></tr>`;
    return;
  }
  body.innerHTML = especialidades.map(e => `
    <tr>
      <td>${e.id}</td>
      <td>${e.nombre}</td>
      <td class="acciones">
        <button onclick="abrirModalEspecialidad(${e.id})" class="btn-editar">Editar</button>
        <button onclick="eliminarEspecialidad(${e.id})"  class="btn-eliminar">Eliminar</button>
      </td>
    </tr>`).join('');
}

function renderTablaServicios() {
  const body = document.getElementById('tabla-servicios-body');
  if (!servicios.length) {
    body.innerHTML = `<tr><td colspan="5" class="empty">No hay servicios.</td></tr>`;
    return;
  }
  body.innerHTML = servicios.map(s => `
    <tr>
      <td>${s.id}</td>
      <td>${s.nombre}</td>
      <td>${s.precio}</td>
      <td>${s.duracion}</td>
      <td class="acciones">
        <button onclick="abrirModalServicio(${s.id})" class="btn-editar">Editar</button>
        <button onclick="eliminarServicio(${s.id})"  class="btn-eliminar">Eliminar</button>
      </td>
    </tr>`).join('');
}

function renderTablaCitas() {
  const body = document.getElementById('tabla-citas-body');
  if (!citas.length) {
    body.innerHTML = `<tr><td colspan="6" class="empty">No hay citas.</td></tr>`;
    return;
  }
  body.innerHTML = citas.map(c => `
    <tr>
      <td>${c.id}</td>
      <td>${c.customer_id  ?? '-'}</td>
      <td>${c.employee_id  ?? '-'}</td>
      <td>${c.fecha        || '-'}</td>
      <td>${c.hora         || '-'}</td>
      <td class="acciones">
        <button onclick="eliminarCita(${c.id})" class="btn-eliminar">Eliminar</button>
      </td>
    </tr>`).join('');
}

function renderTablaComentarios() {
  const body = document.getElementById('tabla-comentarios-body');
  if (!comentarios.length) {
    body.innerHTML = `<tr><td colspan="5" class="empty">No hay comentarios.</td></tr>`;
    return;
  }
  body.innerHTML = comentarios.map(c => `
    <tr>
      <td>${c.id}</td>
      <td>${c.appointment_id}</td>
      <td>${c.texto}</td>
      <td>${c.created_at}</td>
      <td class="acciones">
        <button onclick="eliminarComentario(${c.appointment_id})" class="btn-eliminar">
          Eliminar
        </button>
      </td>
    </tr>`).join('');
}

// ───────────────────────────────────────────────────────────
// 7) Funciones CRUD por sección
//    – Clientes: editarCliente, eliminarCliente
//    – Trabajadores: crearTrabajador, editarTrabajador, eliminarTrabajador
//    – Especialidades: crearEspecialidad, editarEspecialidad, eliminarEspecialidad
//    – Servicios: crearServicio, editarServicio, eliminarServicio
//    – Citas: eliminarCita
//    – Comentarios: eliminarComentario
// ───────────────────────────────────────────────────────────

// —— CLIENTES ——

// Abre el modal con datos de cliente para editar
function abrirModalCliente(id) {
  const modal = document.getElementById('edit-modal');
  const form  = document.getElementById('edit-form');
  // Trae usuario y cliente
  const u = usuarios.find(u => u.id === id) || {};
  const c = clientes.find(c => c.email === u.email) || {};

  // Título del modal
  document.getElementById('modal-title').innerText = `Editar Cliente #${id}`;

  // Construye el formulario, ahora incluyendo apellidos desde 'c'
  form.innerHTML = `
    <label>Nombre de Usuario</label>
    <input name="nombreUsuario" value="${u.nombreUsuario||''}" required>

    <label>Nombre</label>
    <input name="nombre" value="${u.nombre||''}">

    <label>Apellidos</label>
    <input name="apellidos" value="${c.apellidos||''}">

    <label>Email</label>
    <input name="email" type="email" value="${u.email||''}" required>

    <label>Teléfono</label>
    <input name="tlf" value="${c.tlf||''}">

    <label>DNI</label>
    <input name="DNI" value="${c.DNI||''}">

    <label>Dirección</label>
    <input name="direccion" value="${c.direccion||''}">

    <label>Municipio</label>
    <input name="municipio" value="${c.municipio||''}">

    <label>Provincia</label>
    <input name="provincia" value="${c.provincia||''}">

    <!-- Botón de guardar con mismos estilos que editar -->
    <button type="submit" class="btn-editar">
      Guardar cambios
    </button>`;

  // Al enviar, actualiza primero usuario y luego cliente mediante sus rutas correctas
  form.onsubmit = async e => {
    e.preventDefault();
    const btn = form.querySelector('button');
    btn.disabled = true;
    btn.innerText = 'Editando…';

    const data = Object.fromEntries(new FormData(form).entries());
    try {
      // 1) PUT /api/usuarios/{id}
      await fetch(`${API_BASE}/usuarios/${id}`, {
        method: 'PUT',
        headers: HEADERS,
        body: JSON.stringify({
          nombreUsuario: data.nombreUsuario,
          nombre:        data.nombre,
          email:         data.email
        })
      });
      // 2) PUT /api/customers/{clienteId}
      await fetch(`${API_BASE}/customers/${c.id}`, {
        method: 'PUT',
        headers: HEADERS,
        body: JSON.stringify({
          apellidos: data.apellidos,
          tlf:       data.tlf,
          DNI:       data.DNI,
          direccion: data.direccion,
          municipio: data.municipio,
          provincia: data.provincia
        })
      });
      // Refresca datos y tabla
      await cargarTodosLosDatos();
      renderTablaClientes();
      cerrarModal();
    } catch (err) {
      console.error(err);
      alert('Error al guardar cambios de cliente.');
    } finally {
      btn.disabled = false;
      btn.innerText = 'Guardar cambios';
    }
  };

  modal.classList.add('active');
}

// Elimina un cliente
async function eliminarCliente(id) {
  if (!confirm(`Eliminar cliente #${id}?`)) return;
  // Busca primero el objeto cliente
  const c = clientes.find(c=>c.usuario_id===id||c.id===id);
  await fetch(`${API_BASE}/customers/${c.id}`, { method:'DELETE', headers:HEADERS });
  await cargarTodosLosDatos();
  renderTablaClientes();
}

// —— TRABAJADORES ——

// Abre modal para crear o editar trabajador
function abrirModalTrabajador(id=null) {
  const modal = document.getElementById('edit-modal');
  const form  = document.getElementById('edit-form');
  const isNew = id===null;
  const t = empleados.find(t=>t.id===id) || {};
  const u = usuarios.find(u=>u.id===t.usuario_id) || {};
  document.getElementById('modal-title').innerText = isNew ? 'Añadir Trabajador' : `Editar Trabajador #${id}`;
  form.innerHTML = `
    <label>Nombre de Usuario</label>
    <input name="nombreUsuario" value="${u.nombreUsuario||''}" required>
    <label>Nombre</label>
    <input name="nombre"        value="${u.nombre||''}">
    <label>Apellidos</label>
    <input name="apellidos"     value="${u.apellidos||''}">
    <label>Email</label>
    <input name="email" type="email" value="${u.email||''}" required>
    <button type="submit" class="btn-guardar">
      ${isNew?'Crear':'Guardar cambios'}
    </button>`;
  form.onsubmit = async e => {
    e.preventDefault();
    const btn = form.querySelector('button');
    btn.disabled = true; btn.innerText = isNew?'Creando…':'Editando…';
    const data = Object.fromEntries(new FormData(form).entries());
    try {
      if (isNew) {
        // 1) Crear usuario
        const resUser = await fetch(`${API_BASE}/usuarios`, {
          method:'POST', headers:HEADERS,
          body: JSON.stringify({ nombreUsuario:data.nombreUsuario, nombre:data.nombre, apellidos:data.apellidos, email:data.email })
        });
        const newUser = await resUser.json();
        // 2) Crear empleado vinculado
        await fetch(`${API_BASE}/employees`, {
          method:'POST', headers:HEADERS,
          body: JSON.stringify({ usuario_id:newUser.id, especialidades:[] })
        });
      } else {
        // Editar usuario
        await fetch(`${API_BASE}/usuarios/${u.id}`, {
          method:'PUT', headers:HEADERS,
          body: JSON.stringify({ nombreUsuario:data.nombreUsuario, nombre:data.nombre, apellidos:data.apellidos, email:data.email })
        });
        // Podrías editar campos de empleado aquí si los tuvieras
      }
      await cargarTodosLosDatos();
      renderTablaTrabajadores();
      cerrarModal();
    } catch (err) {
      console.error(err);
      alert('Error en operación trabajador.');
    } finally {
      btn.disabled = false;
      btn.innerText = isNew?'Crear':'Guardar cambios';
    }
  };
  modal.classList.add('active');
}

// Eliminar trabajador
async function eliminarTrabajador(id) {
  if (!confirm(`Eliminar trabajador #${id}?`)) return;
  // Borra empleado primero
  await fetch(`${API_BASE}/employees/${id}`, { method:'DELETE', headers:HEADERS });
  // Opcional: borrar usuario si lo deseas:
  // await fetch(`${API_BASE}/usuarios/${id}`, { method:'DELETE', headers:HEADERS });
  await cargarTodosLosDatos();
  renderTablaTrabajadores();
}

// —— ESPECIALIDADES ——

// Abre modal para crear/editar especialidad
function abrirModalEspecialidad(id=null) {
  const modal = document.getElementById('edit-modal');
  const form  = document.getElementById('edit-form');
  const isNew = id===null;
  const e = especialidades.find(e=>e.id===id) || {};
  document.getElementById('modal-title').innerText = isNew?'Añadir Especialidad':`Editar Especialidad #${id}`;
  form.innerHTML = `
    <label>Nombre Especialidad</label>
    <input name="nombre" value="${e.nombre||''}" required>
    <button type="submit" class="btn-guardar">
      ${isNew?'Crear':'Guardar cambios'}
    </button>`;
  form.onsubmit = async ev => {
    ev.preventDefault();
    const btn = form.querySelector('button');
    btn.disabled = true; btn.innerText = isNew?'Creando…':'Editando…';
    const data = Object.fromEntries(new FormData(form).entries());
    try {
      if (isNew) {
        await fetch(`${API_BASE}/specialties`, {
          method:'POST', headers:HEADERS,
          body: JSON.stringify({ nombre:data.nombre })
        });
      } else {
        await fetch(`${API_BASE}/specialties/${id}`, {
          method:'PUT', headers:HEADERS,
          body: JSON.stringify({ nombre:data.nombre })
        });
      }
      await cargarTodosLosDatos();
      renderTablaEspecialidades();
      cerrarModal();
    } catch (err) {
      console.error(err);
      alert('Error especialidad.');
    } finally {
      btn.disabled = false;
      btn.innerText = isNew?'Crear':'Guardar cambios';
    }
  };
  modal.classList.add('active');
}

// Eliminar especialidad
async function eliminarEspecialidad(id) {
  if (!confirm(`Eliminar especialidad #${id}?`)) return;
  await fetch(`${API_BASE}/specialties/${id}`, { method:'DELETE', headers:HEADERS });
  await cargarTodosLosDatos();
  renderTablaEspecialidades();
}

// —— SERVICIOS ——

// Abre modal para crear/editar servicio
function abrirModalServicio(id=null) {
  const modal = document.getElementById('edit-modal');
  const form  = document.getElementById('edit-form');
  const isNew = id===null;
  const s = servicios.find(s=>s.id===id) || {};
  document.getElementById('modal-title').innerText = isNew?'Añadir Servicio':`Editar Servicio #${id}`;
  form.innerHTML = `
    <label>Nombre</label>
    <input name="nombre" value="${s.nombre||''}" required>
    <label>Precio</label>
    <input name="precio" type="number" step="0.01" value="${s.precio||''}" required>
    <label>Duración (min)</label>
    <input name="duracion" type="number" value="${s.duracion||''}" required>
    <button type="submit" class="btn-guardar">
      ${isNew?'Crear':'Guardar cambios'}
    </button>`;
  form.onsubmit = async ev => {
    ev.preventDefault();
    const btn = form.querySelector('button');
    btn.disabled = true; btn.innerText = isNew?'Creando…':'Editando…';
    const data = Object.fromEntries(new FormData(form).entries());
    try {
      if (isNew) {
        await fetch(`${API_BASE}/services`, {
          method:'POST', headers:HEADERS,
          body: JSON.stringify({ nombre:data.nombre, precio:data.precio, duracion:data.duracion })
        });
      } else {
        await fetch(`${API_BASE}/services/${id}`, {
          method:'PUT', headers:HEADERS,
          body: JSON.stringify({ nombre:data.nombre, precio:data.precio, duracion:data.duracion })
        });
      }
      await cargarTodosLosDatos();
      renderTablaServicios();
      cerrarModal();
    } catch (err) {
      console.error(err);
      alert('Error servicio.');
    } finally {
      btn.disabled = false;
      btn.innerText = isNew?'Crear':'Guardar cambios';
    }
  };
  modal.classList.add('active');
}

// Eliminar servicio
async function eliminarServicio(id) {
  if (!confirm(`Eliminar servicio #${id}?`)) return;
  await fetch(`${API_BASE}/services/${id}`, { method:'DELETE', headers:HEADERS });
  await cargarTodosLosDatos();
  renderTablaServicios();
}

// —— CITAS ——

// Eliminar cita
async function eliminarCita(id) {
  if (!confirm(`Eliminar cita #${id}?`)) return;
  await fetch(`${API_BASE}/appointments/${id}`, { method:'DELETE', headers:HEADERS });
  await cargarTodosLosDatos();
  renderTablaCitas();
}

// —— COMENTARIOS ——

// Eliminar comentario de cita
async function eliminarComentario(citaId) {
  if (!confirm(`Eliminar comentario de la cita #${citaId}?`)) return;
  await fetch(`${API_BASE}/appointments/${citaId}/comentarios`, {
    method:'DELETE', headers:HEADERS
  });
  await cargarTodosLosDatos();
  renderTablaComentarios();
}

// ───────────────────────────────────────────────────────────
// 8) Cerrar modal genérico
// ───────────────────────────────────────────────────────────
function cerrarModal() {
  document.getElementById('edit-modal').classList.remove('active');
}

// ───────────────────────────────────────────────────────────
// 9) Cerrar sesión (logout)
// ───────────────────────────────────────────────────────────
async function logout() {
  await fetch(`${API_BASE}/logout`, { method:'POST', headers:HEADERS });
  localStorage.clear();
  window.location.href = 'login.html';
}
