
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
  event.preventDefault(); //Evita que el formulario recargue la página al hacer submit
  //Capturo los valores del formulario
  var nombre = document.getElementById("nombre").value;
  var nombreUsuario = document.getElementById("nombreUsuario").value;
  var email = document.getElementById("email").value;
  var contrasena = document.getElementById("contrasena").value;
  var contrasena_confirmation = document.getElementById("contrasena_confirmation").value;
  //Compruebo desde el frontend que las contraseñas coincidan
  if (contrasena !== contrasena_confirmation) {
    alert("¡Las contraseñas no coinciden!");
    return;
  }
  //Usamos fetch para poder hacer peticiones HTTP y HTTPS al backend.
  fetch("http://localhost:8001/sanctum/csrf-cookie", {
    method: "GET", //Método de la petición
    credentials: 'include', //Hace que que se envíen las credenciales con la petición
    headers: {
      "Accept": "application/json", //Le dice a Laravel que está esperando una respuesta JSON
    },
  })
  .then(() => {
    var csrfToken = getCookie("XSRF-TOKEN"); //Guardo en una variable el token
    return fetch("http://localhost:8001/register", {  //Devuelve la consulta POST a la ruta registro añadiendo el token en el encabezado de la petición
      method: "POST",
      headers: {
        "Content-Type": "application/json", //Esto le dice al servidor cómo se van a enviar los datos del cuerpo de la solicitud, en mi caso en formato JSON
        "X-XSRF-TOKEN": csrfToken, //Aquí se añade el token 
        "Accept": "application/json",
      },
      credentials: 'include',
      body: JSON.stringify({ //Este es el cuerpo de la solicitud, que contiene los datos del formulario, donde se refiere el encabezado "Content-Type".
        nombre: nombre,
        nombreUsuario: nombreUsuario,
        email: email,
        contrasena: contrasena,
        contrasena_confirmation: contrasena_confirmation,
      }),
    });
  })
  .then(async response => { //Aquí se maneja la respuesta del servidor, uso una función async para poder usar await dentro de ella, que me permitirá pausar la ejecución hasta que se resuelva la promesa.
    var data = await response.json(); // Guardo en una variable la respuesta del servidor en formato JSON. Uso await 

    // Aquí se muestran los errores del backend 
    if (!response.ok) {
      if (data.errors) {
        // Si Laravel devuelve errores de validación
        var mensajes = Object.values(data.errors).flat().join("\n"); //Aquí guardo en una variable los errores usando object.values para obtener los valores del objeto de errores, luego los junto en un array con flat y los convierto en una cadena con un salto de linea por cada linea con join("\n").
        alert("Errores en el registro:\n" + mensajes); // Muestro los errores en un alert
      } else {
        alert("Error: " + (data.message || "Error desconocido")); // En caso de que no haya errores de validación, muestro el mensaje de error que devuelve el servidor o el mensaje por defecto que es Error desconocido en este caso.
      }
      throw new Error("Registro fallido"); // Lanzamos un error para que no se ejecute el siguiente código y se interprete en el .catch
    }

    // Registro exitoso
    alert(data.message || "¡Registro exitoso!");
    window.location.href = "login.html"; // Redirige a la página de inicio de sesión después de un registro exitoso
  })
  .catch(error => { // Aquí capturamos cualquier error que no sea de validación
    console.error("Error en el registro:", error);
  });
}

//Función para iniciar sesión
  function login(event) {
    event.preventDefault(); //Evita que el formulario recargue la página al hacer submit
    var email = document.getElementById("email").value; //Capturo el email del formulario
    var contrasena = document.getElementById("contrasena").value; //Capturo la contraseña del formulario

    fetch("http://localhost:8001/sanctum/csrf-cookie", { //Petición para obtener el token CSRF
      method: "GET", //Método de la petición
      credentials: 'include', //Hace que que se envíen las credenciales con la petición
      headers: {
        "Accept": "application/json", //Le dice a Laravel que está esperando una respuesta JSON
      },
    })
    .then(() => {
      var csrfToken = getCookie("XSRF-TOKEN"); //Guardo en una variable el token
      return fetch("http://localhost:8001/login", { //Devuelve la consulta POST a la ruta login añadiendo el token en el encabezado de la petición
        method: "POST",
        headers: {
          "Content-Type": "application/json", //Esto le dice al servidor cómo se van a enviar los datos del cuerpo de la solicitud, en mi caso en formato JSON
          "X-XSRF-TOKEN": csrfToken, //Aquí se añade el token 
          "Accept": "application/json",
        },
        credentials: 'include',
        body: JSON.stringify({ //Este es el cuerpo de la solicitud, que contiene los datos del formulario, donde se refiere el encabezado "Content-Type".
          email: email,
          contrasena: contrasena,
        }),
      });
    })
    .then(async response => { //Aquí se maneja la respuesta del servidor, uso una función async para poder usar await dentro de ella, que me permitirá pausar la ejecución hasta que se resuelva la promesa.
      var data = await response.json(); // Guardo en una variable la respuesta del servidor en formato JSON. Uso await 

      // Aquí se muestran los errores del backend 
      if (!response.ok) {
        if (data.errors) {
          // Si Laravel devuelve errores de validación
          var mensajes = Object.values(data.errors).flat().join("\n"); //Aquí guardo en una variable los errores usando object.values para obtener los valores del objeto de errores, luego los junto en un array con flat y los convierto en una cadena con un salto de linea por cada linea con join("\n").
          alert("Errores en el login:\n" + mensajes); // Muestro los errores en un alert
        } else {
          alert("Error: " + (data.message || "Error desconocido")); // En caso de que no haya errores de validación, muestro el mensaje de error que devuelve el servidor o el mensaje por defecto que es Error desconocido en este caso.
        }
        throw new Error("Login fallido"); // Lanzamos un error para que no se ejecute el siguiente código y se interprete en el .catch
      }

      // Login exitoso
      alert(data.message || "Login exitoso!");
      window.location.href = "index.html"; // Redirige a la página de inicio de sesión después de un login exitoso
    })
    .catch(error => { // Aquí capturamos cualquier error que no sea de validación
      console.error("Error en el login:", error);
    });
}

//Función para comprobar si el usuario está autenticado y mostrar el nav correspondiente
function initAuth() {
  // No ejecutar en páginas de login/register
  if (window.location.pathname.includes('login.html') || window.location.pathname.includes('register.html')) return;
  
  fetch('http://localhost:8001/getUser', {
    method:      'GET',
    credentials: 'include',
    headers:     { 'Accept': 'application/json' }
  })
  .then(response => {
    if (!response.ok) throw new Error('No autenticado');
    return response.json();
  })
  .then(user => {
    mostrarUserNav(user); // Si el usuario está autenticado, muestra el nav correspondiente
  })
  .catch(err => {
    console.log('No logueado', err);
  });
}

function mostrarUserNav(user) {
  // Oculta enlaces de login/register
  document.getElementById('nav-login').style.display    = 'none';
  document.getElementById('nav-register').style.display = 'none';

  var ul = document.querySelector('nav ul');

  //Creo el li del usuario y pongo el mensaje para saludarlo
  var liUser = document.createElement('li');
  liUser.textContent = `Hola, ${user.name}`;
  ul.appendChild(liUser);

  // Creo el botón de logout
  var liLogout = document.createElement('li'); // Creo un nuevo elemento <li>
  var btnLogout = document.createElement('button'); //Creo un nuevo elemento <button>
  btnLogout.id   = 'btn-logout'; //Le asigno un id
  btnLogout.textContent = 'Logout'; //Le asigno el texto del botón
  liLogout.appendChild(btnLogout); //Añado el botón al <li>
  ul.appendChild(liLogout);// Añade el <li> al <ul>

  // Conecta el click al logout
  btnLogout.addEventListener('click', logout);

function logout() {
  fetch('http://localhost:8001/logout', {
    method:      'POST',
    credentials: 'include',
    headers:     {
      'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      'Accept':       'application/json'
    }
  })
  .then(() => {
    // Después de cerrar sesión, recargamos para mostrar el nav de invitado
    window.location.reload();
  });
}
}

/*function register(event) {
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
  .then(async response => {
    const data = await response.json();

    if (!response.ok) {
      // Aquí se muestran los errores del backend (por ejemplo, email ya registrado)
      if (data.errors) {
        // Si Laravel devuelve errores de validación
        let mensajes = Object.values(data.errors).flat().join("\n");
        alert("Errores en el registro:\n" + mensajes);
      } else {
        alert("Error: " + (data.message || "Error desconocido"));
      }
      throw new Error("Registro fallido");
    }

    // Registro exitoso
    alert(data.message || "¡Registro exitoso!");
    window.location.href = "login.html";
  })
  .catch(error => {
    console.error("Error en el registro:", error);
  });
}*/

document.addEventListener('DOMContentLoaded', () => {
  initAuth();
});
