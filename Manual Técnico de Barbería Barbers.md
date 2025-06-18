# Manual Técnico de Barbería Barbers

**Versión:** 1.0
**Autor:** Francisco Miguel Rodríguez Bras / Seyte SL
**Fecha:** 18 de junio de 2025

---

## Índice

1. [Visión general del sistema](#visión-general-del-sistema)
2. [Tecnologías empleadas](#tecnologías-empleadas)
3. [Instalación](#instalación)
4. [Estructura de carpetas](#estructura-de-carpetas)
5. [Despliegue](#despliegue)
6. [Seeders](#seeders)
7. [Rutas](#rutas)
8. [Base de datos](#base-de-datos)
9. [Diagrama de casos de uso](#diagrama-de-casos-de-uso)
10. [Aplicación](#aplicación)

    * [Registro de usuario](#funcionalidad-registro-de-usuario)
    * [Login de usuario](#funcionalidad-login-de-usuario)
    * [Flujo de usuario](#flujo-de-usuario)
    * [Reserva de citas](#reserva-de-citas)
    * [Citas en perfil](#citas-en-perfil)

---

## Visión general del sistema

La aplicación web se encarga de la gestión de citas por parte de los clientes, así como la interacción de los mismos con la reciprocidad del servicio recibido. En esta versión contamos con unos pilares básicos pero con un alto grado de escalabilidad.

La aplicación trabaja con los siguientes bloques: usuarios, clientes, empleados, especialidades, contratos, servicios.

---

## Tecnologías empleadas

* **Backend:** Laravel 11 (PHP 8.2)
* **Base de datos:** MySQL 8
* **Contenedores:** Docker & Docker Compose
* **Frontend:** HTML, CSS y JavaScript estático

---

## Instalación

1. **Preparar carpeta de proyecto**

   ```bash
   mkdir Barberia && cd Barberia
   ```
2. **Clonar repositorio**

   ```bash
   git clone https://github.com/Franmii6/TFG-DAW.git .
   ```
3. **Estructura tras clonar**

   * `Backend/`
   * `Frontend/`
   * Manuales (`Manual_Tecnico.md`, `Manual_Usuario.md`) y `README.md`

---

## Estructura de carpetas

```text
Barberia/                   ← Carpeta raíz del proyecto
├── Backend/               ← Código y configuración de Laravel y Docker
│   ├── src/               ← Código fuente Laravel
│   │   ├── app/           ← Lógica (Controllers, Models, etc.)
│   │   ├── config/        ← Archivos de configuración
│   │   ├── database/      ← Migraciones y seeders
│   │   ├── public/        ← Punto de entrada (index.php)
│   │   └── routes/        ← Definición de rutas (web.php, api.php)
│   ├── vendor/            ← Dependencias de Composer
│   ├── nginx/             ← Configuración de Nginx
│   ├── mysql/             ← Volumen de datos de MySQL
│   ├── Dockerfile         ← Imagen PHP-FPM
│   ├── docker-compose.yml ← Orquestación de contenedores
│   └── .env.example       ← Variables de entorno de Laravel
│
├── Frontend/              ← Código estático del cliente
│   ├── assets/            ← Imágenes, fuentes, iconos…
│   ├── styles/            ← CSS o SCSS
│   ├── js/                ← JavaScript
│   ├── index.html         ← Pantalla principal
│   ├── login.html         ← Formulario de acceso
│   ├── register.html      ← Formulario de registro
│   └── pagina2.html       ← Otras vistas estáticas
│
├── Manual_Tecnico.md      ← Este documento
├── Manual_Usuario.md      ← Documento de usuario
└── README.md              ← Descripción general del repositorio
```

---

## Despliegue

1. Abrir Docker en tu sistema.
2. Entrar en la carpeta `Backend`.
3. Ejecutar:

   ```bash
   docker-compose up -d
   ```
4. Acceder en el navegador a `http://localhost:8001`.

### Explicación de `docker-compose.yml`

* **Networks:** Define la red `laravel` (driver: bridge).
* **Services:**

  * **nginx**

    * `image: nginx:alpine`
    * `ports: "80:8001"`
    * Monta volúmenes de código y configuración.
  * **mysql**

    * `image: mysql`
    * `ports: "3306:4306"`
    * Variables de entorno para base de datos.
    * `TZ: Europe/Madrid`
  * **php**

    * `build: context . , dockerfile Dockerfile`
    * `ports: "9000:9000"`
    * Monta `src` en `/var/www/html`.

**Importante:** ejecutar el seeder inicial:

```bash
docker exec -it php sh
php artisan migrate:fresh --seed
```

---

## Seeders

* Único archivo: `database/seeders/DatabaseSeeder.php`.
* Se encarga de poblar la base de datos con datos iniciales (incluyendo administrador).

---

## Rutas

* Ubicación: `routes/api.php`
* **Públicas:**

  * `POST /api/register`
  * `POST /api/login`
  * `GET /api/services`
  * `GET /api/comentarios/recientes`
  * `GET /api/employees`
* **Privadas:** requieren `Authorization: Bearer <token>`

  * `POST /api/logout`
  * CRUD de usuarios, citas, servicios, comentarios, etc.

---

## Base de datos

*(Incluir diagrama y detalles de tablas según convenga)*

---

## Diagrama de casos de uso

*(Incluir imagen o diagrama de casos de uso aquí)*

---

## Aplicación

### Funcionalidad: Registro de usuario

1. **Formulario:** `register.html` con campos:

   * `nombre`, `nombreUsuario`, `email`, `contrasena`, `contrasena_confirmation`.
2. **Frontend:** previene envío, valida contraseñas iguales, envía `POST /api/register`.
3. **Backend:**

   ```php
   Route::post('register', [AuthController::class, 'register']);
   ```

   * Validaciones Laravel:

     | Campo           | Reglas                                                    |
     | --------------- | --------------------------------------------------------- |
     | `nombre`        | required, string, max:255                                 |
     | `nombreUsuario` | required, string, max:255, unique\:usuarios,nombreUsuario |
     | `email`         | required, email, unique\:usuarios,email                   |
     | `contrasena`    | required, string, min:8, confirmed                        |
   * Respuestas:

     * `201 Created`: registro correcto.
     * `422 Unprocessable Entity`: errores de validación.
     * `500 Internal Server Error`: fallo inesperado.
4. **Flujo final:** tras `201`, alerta de éxito y redirección a `login.html`.

### Funcionalidad: Login de usuario

1. **Formulario:** `login.html` con `email` y `contrasena`.
2. **Frontend:** previene envío, envía `POST /api/login`.
3. **Backend:**

   ```php
   Route::post('login', [AuthController::class, 'login']);
   ```

   * Validaciones:

     | Campo        | Reglas          |
     | ------------ | --------------- |
     | `email`      | required,email  |
     | `contrasena` | required,string |
   * Códigos:

     * `200 OK`: credenciales válidas.
     * `401 Unauthorized`: credenciales incorrectas.
     * `422 Unprocessable Entity`: validación.
4. **Flujo final:** guarda `token`, `token_time`, `user`, `nombreUsuario` en `localStorage` y redirige a `index.html`.

### Flujo de usuario general

* **Perfil:** `perfil.html` carga `initAuth()` para validar token.
* **Obtener datos:** `GET /api/profile` → muestra datos de usuario, cliente y contrato.
* **Editar perfil:** `PUT /api/profile` con campos de cliente.

### Reserva de citas

* **Página de servicios:** `pagina3.html` carga:

  1. `cargarComentariosRecientes()` → `GET /api/comentarios/recientes`
  2. `cargarServicios()` → `GET /api/services`
* **Modal de reserva:**

  * `llenarSelectEmpleados()`, `configurarFechaMinima()`.
  * `refrescarFranjasDisponibles()` → `GET /api/services/{id}/available-slots?fecha=YYYY-MM-DD`
* **Confirmar reserva:**

  * Si no hay contrato, `POST /api/customers/{id}/contracts`.
  * Luego `POST /api/appointments` con datos de la cita.

### Citas en perfil

* **Cargar citas:** `GET /api/customers/{id}/appointments`.
* **Acciones:**

  * Cancelar (`DELETE /api/appointments/{id}`).
  * Evaluar (`POST /api/appointments/{id}/comentarios`).
  * Ver opinión (`GET /api/appointments/{id}/comentarios`).

---

*Fin del Manual Técnico*
