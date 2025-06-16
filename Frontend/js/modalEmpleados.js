// Abre y rellena el modal con los datos del empleado
function mostrarEmpleadoModal(emp) {
  document.getElementById('modalName').innerText = `${emp.usuario.nombre} ${emp.apellidos}`;
  document.getElementById('modalPhoto').src      = emp.photoUrl || 'assets/imges/equipo/placeholder.png';
  document.getElementById('modalPhoto').alt      = `${emp.usuario.nombre} ${emp.apellidos}`;

  document.getElementById('modalExperience').innerText = `${emp.anos_experiencia} años de experiencia`;

  const ul = document.getElementById('modalSpecialties');
  ul.innerHTML = '';
  emp.especialidades.forEach(sp => {
    const li = document.createElement('li');
    li.innerText = sp.nombre; // o como llames al campo
    ul.appendChild(li);
  });

  document.getElementById('empleadoModal').style.display = 'block';
}

// Cierra el modal genérico
function closeEmployeeModal() {
  document.getElementById('empleadoModal').style.display = 'none';
}

// Cerrar al clicar fuera
window.addEventListener('click', e => {
  const modal = document.getElementById('empleadoModal');
  if (e.target === modal) closeEmployeeModal();
});



document.addEventListener('DOMContentLoaded', async () => {
      await cargarEquipo();
});