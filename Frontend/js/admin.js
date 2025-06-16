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

async function cargarDatos() {
  try {
    const [resU, resC, resT, resE, resS, resEE] = await Promise.all([
      fetch(`${API_URL}/usuarios`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
      fetch(`${API_URL}/customers`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
      fetch(`${API_URL}/employees`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
      fetch(`${API_URL}/specialties`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
      fetch(`${API_URL}/services`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
      fetch(`${API_URL}/employees/{employee}/specialties`, { headers: { 'Authorization': `Bearer ${TOKEN}` } }),
    ]);
    if (!resU.ok || !resC.ok || !resT.ok || !resE.ok || !resS.ok || !resEE.ok) {
      throw new Error('Error cargando datos de API');
    }
    cacheDeDatos.usuarios = await resU.json();
    cacheDeDatos.clientes = await resC.json();
    cacheDeDatos.trabajadores = await resT.json();
    cacheDeDatos.especialidades = await resE.json();
    cacheDeDatos.servicios = await resS.json();
    cacheDeDatos.empleadoEspecialidades = await resEE.json();
  } catch (err) {
    console.error(err);
    alert('Error al cargar datos: ' + err.message);
  }
}

function mostrarSeccion(idSeccion) {
  document.querySelectorAll('.seccion').forEach(sec => sec.classList.remove('activa'));
  const sec = document.getElementById(`seccion-${idSeccion}`);
  if (!sec) return;
  sec.classList.add('activa');
  document.getElementById('admin-title').innerText = `Panel de Administración - ${idSeccion.charAt(0).toUpperCase() + idSeccion.slice(1)}`;
  if (idSeccion === 'clientes') renderClientes();
  else if (idSeccion === 'trabajadores') {
    renderClientes();
    renderTrabajadores();
    renderEspecialidades();
  } else if (idSeccion === 'servicios') renderServicios();
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
        <td class="acciones">
          <button data-action="edit" data-entity="cliente" data-id="${u.id}">Editar</button>
          <button data-action="delete" data-entity="cliente" data-id="${u.id}">Eliminar</button>
        </td>
      </tr>`;
  }).join('') : '<tr><td colspan="10">No hay usuarios/clientes.</td></tr>';
  delegateTableActions(body);
}

function renderTrabajadores() {
  const { trabajadores, empleadoEspecialidades } = cacheDeDatos;
  const body = document.getElementById('tabla-trabajadores-body');
  body.innerHTML = trabajadores.map(t => {
    const ids = empleadoEspecialidades.filter(pe => pe.empleado_id===t.id).map(pe=>pe.especialidad_id).join(', ');
    return `
      <tr>
        <td>${t.id}</td>
        <td>${t.nombreUsuario}</td>
        <td>${t.nombre}</td>
        <td>${t.email}</td>
        <td>${ids||'Sin Especialidad'}</td>
        <td>
          <button data-action="edit" data-entity="trabajador" data-id="${t.id}">Editar</button>
          <button data-action="delete" data-entity="trabajador" data-id="${t.id}">Eliminar</button>
        </td>
      </tr>`;
  }).join('');
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
        <button data-action="edit" data-entity="especialidad" data-id="${e.id}">Editar</button>
        <button data-action="delete" data-entity="especialidad" data-id="${e.id}">Eliminar</button>
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
        <button data-action="edit" data-entity="servicio" data-id="${s.id}">Editar</button>
        <button data-action="delete" data-entity="servicio" data-id="${s.id}">Eliminar</button>
      </td>
    </tr>`).join('');
  delegateTableActions(body);
}

function delegateTableActions(tbody) {
  tbody.querySelectorAll('button').forEach(btn => {
    const { action, entity, id } = btn.dataset;
    if (action==='edit') btn.onclick = () => abrirModalEdicion(entity, id);
    else if (action==='delete') btn.onclick = () => eliminarEntidad(entity, id);
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

async function logout() {
  await fetch(`${API_URL}/logout`,{method:'POST',headers:{'Authorization':`Bearer ${TOKEN}`}});
  localStorage.clear(); window.location.href='login.html';
}

// Init

document.addEventListener('DOMContentLoaded', async ()=>{
  if(!TOKEN){ alert('Login requerido'); window.location.href='login.html'; return; }
  setupEventListeners();
  await cargarDatos();
  mostrarSeccion('clientes');
});
