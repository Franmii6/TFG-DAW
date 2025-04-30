
//Función para obtener el token CSRF. Cuestión 3.
function getCookie(name) {
  var value =  document.cookie //Esto es para obtener la cookie de sesión porque al hacer el fetch se guardan las cookies en el navegador
    .split("; ") // Como el document.cookie devuelve un string con todas las cookies, lo que hacemos es separarlas por el "; " para obtener un array de cookies
    .find(row => row.startsWith(name + "=")) // Buscamos la cookie que empieza con el nombre que le pasamos
    ?.split("=")[1];// Esto es para obtener el valor de la cookie, ya que separamos el string del xsrf-token con el =, y nos deja un array con dos argumentos, uno es el nombre y el segundo, que es el que pedimos, el token.
  return value ? decodeURIComponent(value) : undefined; // ¡Decodifica el valor!
}

//Función para registrar un usuario
function register(event) {
  event.preventDefault();

  var nombre = document.getElementById("nombre").value;
  var nombreUsuario = document.getElementById("nombreUsuario").value;
  var email = document.getElementById("email").value;
  var contrasena = document.getElementById("contrasena").value;
  var contrasena_confirmation = document.getElementById("contrasena_confirmation").value;

  // Validación frontend de contraseña
  if (contrasena !== contrasena_confirmation) {
    alert("¡Las contraseñas no coinciden!");
    return;
  }

  //Obtenemos el token CSRF para poder hacer las peticiones
  // Solicitud para obtener el token CSRF
  fetch("http://localhost:8001/sanctum/csrf-cookie", {
    method: "GET",
    credentials: 'include', // Permite cookies cross-origin
    headers: {
        "Accept": "application/json", // Añade esto
    },
  })
  .then (() => {
    //Leemos el token CSRF de la cookie
    var csrfToken = getCookie("XSRF-TOKEN"); // Esto es para obtener el token CSRF de la cookie que se guarda en el navegador al hacer la petición anterior
    console.log("Token CSRF:", csrfToken);
    return fetch("http://localhost:8001/register", { //Duda: Si uso fetch no necesito nada mas??
      method: "POST",
      headers: {
          "Content-Type": "application/json",
          "X-XSRF-TOKEN": csrfToken, // Añadimos el token CSRF a la cabecera de la petición para que se envíe al back y lo valide.
          "Accept": "application/json",
      },
      credentials: 'include', // Esto es para que me guarde la cookie de sesión
      body: JSON.stringify({ // Aquí tengo que poner las variables con el nombre que tienen en el back?
          nombre: nombre,
          nombreUsuario: nombreUsuario,
          email: email,
          contrasena: contrasena,
          contrasena_confirmation: contrasena_confirmation,
      }),
  })
  .then(response => response.json())
  .then(data => {
    console.log(data);
    if (data.message) {
      alert(data.message);
      window.location.href = "login.html";
    } else if (data.errors) {
      alert(JSON.stringify(data.errors));
    }
    })
  .catch(error => console.error("Error:", error));
  })
}
