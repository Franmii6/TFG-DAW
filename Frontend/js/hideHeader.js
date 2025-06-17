//Declaraciones
let lastScrollTop = 0; //guarda la posición vertical del scroll en el momento anterior. Se inicializa en 0.
let isScrolling; // Variable para controlar el tiempo de inactividad del scroll
const header = document.querySelector("header"); // Selecciona el elemento header del DOM

// Oculta y muestra el header al hacer scroll
window.addEventListener("scroll", function () { // Evento que se dispara al hacer scroll
  let scrollTop = // Obtiene la posición vertical del scroll
    window.scrollY || document.documentElement.scrollTop; // Compatibilidad con navegadores antiguos

  if (scrollTop > lastScrollTop) { // Si el scroll es hacia abajo
    header.style.top = "-20%"; // Mueve el header hacia arriba fuera de la vista
  } else { // Si el scroll es hacia arriba
    header.style.top = "0";// Muestra el header
  }

  clearTimeout(isScrolling); // Limpia el timeout anterior para evitar múltiples ejecuciones
  isScrolling = setTimeout(function () { // Establece un nuevo timeout
    header.style.top = "0"; // Asegura que el header se muestre después de un tiempo de inactividad
  }, 1000); // Tiempo en milisegundos para considerar inactividad

  lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Actualiza la posición del scroll anterior
});