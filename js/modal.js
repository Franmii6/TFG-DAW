// Abrir la modal correspondiente
function openModal(index) {
  const modal = document.getElementById(`modal${index}`);
  const blur = document.getElementById("blur");
  modal.style.display = "block";
  blur.style.display = "block";
}

// Cerrar la modal correspondiente
function closeModal(index) {
  const modal = document.getElementById(`modal${index}`);
  const blur = document.getElementById("blur");
  modal.style.display = "none";
  blur.style.display = "none";
}

//Resetear los modal que estan abiertos
function resetAll() {
  document
    .querySelectorAll(".modal")
    .forEach((modal) => (modal.style.display = "none"));
  document.getElementById("blur").style.display = "none";
}
