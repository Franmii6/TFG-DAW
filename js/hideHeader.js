//Declaraciones
let lastScrollTop = 0;
let isScrolling;
const header = document.querySelector("header");

// Oculta y muestra el header al hacer scroll
window.addEventListener("scroll", function () {
  let scrollTop =
    window.scrollY || document.documentElement.scrollTop;

  if (scrollTop > lastScrollTop) {
    header.style.top = "-20%"; // Ajusta según el tamaño de tu cabecera
  } else {
    header.style.top = "0";
  }

  clearTimeout(isScrolling);
  isScrolling = setTimeout(function () {
    header.style.top = "0";
  }, 1000);

  lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});