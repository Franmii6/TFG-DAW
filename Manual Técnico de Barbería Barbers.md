# Manual Técnico de Barbería Barbers

**Versión:** 1.0  
**Autor:** Francisco Miguel Rodríguez Bras / Seyte SL  
**Fecha:** 18 de junio de 2025

---

## Índice

- [Manual Técnico de Barbería Barbers](#manual-técnico-de-barbería-barbers)
  - [Índice](#índice)
  - [Visión general del sistema](#visión-general-del-sistema)
  - [Tecnologías empleadas y requisitos](#tecnologías-empleadas-y-requisitos)
  - [Modelo de datos?](#modelo-de-datos)
  - [Instalación](#instalación)
    - [Preparar carpeta de proyecto](#preparar-carpeta-de-proyecto)
  - [Clonar repositorio](#clonar-repositorio)
  - [Estructura de carpetas](#estructura-de-carpetas)
  - [Variables del entorno clave hablar del .env.example?](#variables-del-entorno-clave-hablar-del-envexample)
  - [Despliegue](#despliegue)
  - [Seeders](#seeders)
  - [Rutas](#rutas)
  - [Aplicación](#aplicación)

---

## Visión general del sistema

La aplicación web se encarga de la gestión de citas por parte de los clientes, así como la interacción de los mismos con la reciprocidad del servicio recibido.  
En esta versión de la aplicación contamos con unos pilares básicos pero con un alto grado de escalabilidad.  
Los bloques principales son: **usuarios**, **clientes**, **empleados**, **especialidades**, **contratos** y **servicios**.

---

## Tecnologías empleadas y requisitos

- **Backend**: PHP 8.
- **Base de datos**: MySQL 8  
- **Contenedores**: Docker & Docker Compose  
- **Frontend**: HTML, CSS y JavaScript estático

---

## Modelo de datos?

| Tituo90 1 | Titulo 2 |
| - | - |
| Copluman 1 | string |


## Instalación

1. **Preparar carpeta de proyecto**  
    Crea una carpeta llamada `Barberia` y sitúate en ella:

### Preparar carpeta de proyecto

mkdir Barberia && cd Barberia

## Clonar repositorio

git clone <https://github.com/Franmii6/TFG-DAW.git> .

2. **Tras clonar verás dos carpetas principales:**

- Backend/

- Frontend/

Además de los manuales (Manual_Tecnico.md, Manual_Usuario.md) y README.md.

## Estructura de carpetas

Barberia/                   ← Carpeta raíz del proyecto
├── Backend/               ← Código y configuración de Laravel y Docker
│   ├── src/               ← Código fuente Laravel
│   │   ├── app/           ← Lógica (Controllers, Models, etc.)
│   │   ├── config/        ← Archivos de configuración
│   │   ├── database/      ← Migraciones y seeders
│   │   ├── public/        ← Punto de entrada (index.php)
│   │   └── routes/        ← Definición de rutas (web.php, api.php)
│   ├── vendor/            ← Dependencias de Composer
│   ├── nginx/             ← Configuración de Nginx (default.conf)
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

## Variables del entorno clave hablar del .env.example?

Comentar el .env que tengo en .env.examlple

- DOCKERFILE:
    # Usa la imagen oficial de php, versión 8.2, etiqueta fpm-alpine
    FROM php:8.2-fpm-alpine

    # Establece el directorio de trabajo
    WORKDIR /var/www/html

    # Actualizamos los paquetes e instalamos dependencias básicas como curl para peticiones HTTP, y librerías para imágenes (png, xml)
    RUN apk update && apk add \
        curl \  
        libpng-dev \
        libxml2-dev \
        zip \
        unzip

    # Instalamos extensiones de PHP necesarias para bases de datos (MySQL) y también Node.js y npm para gestionar dependencias de frontend
    RUN docker-php-ext-install pdo pdo_mysql \
        && apk --no-cache add nodejs npm

    # Copiamos el código fuente de la aplicación al contenedor
    COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

    # Cambiamos al usuario root para poder modificar permisos de archivos
    USER root

    # Damos permisos de escritura completos (777) al directorio de la aplicación.
    RUN chmod 777 -R /var/www/html

## Despliegue

Para realizar el despliegue debemos:
    - Abrir docker según el sistema operativo que tengas en tu dispositivo
    - Entrar en la carpeta de backend
    - Ejecutar el comando docker-compose up, puede ponerle el párametro -d que lo ejecutará en segundo plano y podrás utilizar esa misma terminal.
    -  Esto ejecutará el archivo docker-compose.yml que tenemos en la carpeta Backend
       -  ## EXPLICACIÓN archivo docker-compose.yml:
          - Indicamos con networks la red, laravel es el nombre y driver:bridge indica que los contenedor conectados a la red pueden comunicarse entre ellos.
          - En services se encuentran los contenedores (nginx, mysql y php):
            -NGINX:
              -  image: nginx:alpine utiliza la imagen nginx con la etiqueta alpine
              -  container_name: nginx es el nombre del contenedor
              -  restart: unless-stopped: Se reiniciará el contenedor automáticamente a menos que se le indique
              -  tty: true: Asignarle un terminal al contenedor
              -  Ports: Usa el puerto 80 para escuchar y desplegar la aplicación por el puerto 8001
              -  volumes:
                 -  ./src:/var/www/html: Para indicar donde está el directorio de Laravel
                 -  /nginx/default.conf:/etc/nginx/conf.d/default.conf: Monta el archivo de configuración local en la ubicación del Nginx dentro del contenedor.
              - Depends on: Para indicar que el contenedor nginx depende de php, asi que montará primero el de php
              - networks: -laravel: Indica que el contenedor está conectado a la red de laravel.
            - MYSQL:
              - paltform: linux/amd: Especifica la arquitectura de la imagen
              - images: mysql: indica la imagen
              - container_name: indica el nombre del contenedor
              - command: --lower-case-table-names=2: Ejecuta MySQL con un argumento de comando específico que configura la forma en que MySQL maneja los nombres de tablas (los convierte a minúsculas y permite comparaciones insensibles a mayúsculas/minúsculas).
              - restart: unless-stopped: Se reiniciará el contenedor automáticamente a menos que se le indique
              - tty: true: Asignarle un terminal al contenedor
              - Mapea el puerto 3306 del contenedor (puerto estándar de MySQL) al puerto 4306 de mi máquina. La conexión desde la máquina local se hará desde el puerto 4306.
              - ./mysql:/var/lib/mysql: Monta el directorio local mysql (en la misma ubicación que docker-compose.yml) al directorio /var/lib/mysql.
              - environment:: Define variables de entorno para configurar MySQL.
              - MYSQL_DATABASE: laravel_docker: El nombre de la base de datos que se creará automáticamente.
              - MYSQL_USER: user: El nombre del usuario de la base de datos que se creará.
              - MYSQL_PASSWORD: user: La contraseña para el usuario definido.
              - MYSQL_ROOT_PASSWORD: root: La contraseña para el usuario root de MySQL.
              - SERVICE_TAGS: dev: Una variable de entorno personalizada, posiblemente para propósitos de monitoreo o descubrimiento de servicios.
              - SERVICE_NAME: mysql: Otra variable de entorno personalizada, indicando el nombre del servicio.
              - TZ: Europe/Madrid: Establece la zona horaria dentro del contenedor a Europe/Madrid.
              - networks: -laravel: Indica que el contenedor está conectado a la red de laravel.
            - PHP:
              - php:: El nombre del servicio.
              - build:: Indica que la imagen de este servicio se construirá a partir de un Dockerfile.
                - context: .: Para indicar que se encuentra en la misma ubicación
                - dockerfile: Dockerfile: El archivo dockerfile que se ejecutará
              - container_name: php: Asigna el nombre php al contenedor.
              - restart: unless-stopped: Se reiniciará el contenedor automáticamente a menos que se le indique
              - tty: true: Asignarle un terminal al contenedor
              - working_dir: /var/www/html: Define el directorio de trabajo dentro del contenedor, donde tu código Laravel espera residir.
              - ./src:/var/www/html: Monta el directorio src de tu máquina local en /var/www/html dentro del contenedor PHP.
              - ports: 9000:9000: Mapea el puerto 9000 del contenedor al puerto 9000 de tu máquina local. Este es el puerto donde PHP escuchará las solicitudes de Nginx.
              - depends_on: mysql: El servicio PHP depende del servicio mysql para su base de datos.
              - networks: -laravel: Indica que el contenedor está conectado a la red de laravel.

Una vez hecho el despliegue y se ha realizado correctamente, podemos ir al navegador y poner localhost:8001 y se nos verá la aplicación.

IMPORTANTE!!
Hacer primero un seeder para poblar la base de datos para incluir el administrador desde el inicio, para gestionar la base de datos desde 0 con el admin si lo quisiese.
Ejecutar: 
    - Una vez hecho docker-compose up -d
    - Entramos al contenedor de php ejecutando el comando: docker exec -it php sh
    -  Luego una vez dentro hacemos php artisan migrate:fresh --seed
        Esto hace una migración, eliminando lo que había antes y ejecutando --seed para poblar la base de datos según las indicaciones de DatabaseSeeder.

## Seeders

Los Seeders que tengo que creado es un único archivo llamado DatabaseSeeder que se encuentra en database Seeders, que es el que se encarga de crear los seeders para poblar la base de datos.
 Utilizan los factories que son como las fábricas para crear lo que se le indicque, para indicarlo se hará en el archivo DatabaseSeeder.

## Rutas

Las rutas se encuentran en routes/api.php

Hay dos bloques de rutas, las públicas y las privadas (Que necesitan autenticación)

Las rutas públicas son el post de registro, para que se registre el usuario y el post de login, para que pueda acceder el usuario

Después hay tres rutas de get, que son services y comentarios/recientes, para mostrar los servicios y los últimos comentarios en la página de servicios, y la de employees, que muestran los empleados en la página de equipo.

En el bloque de rutas privadas tenemos casi todas las rutas, como son el logout, para salir de la sesión del loggeado, delete de usuarios, citas, servicios para eliminar, el update para actualizar el cliente, el usuario o la especialidad, y post para reservar citas, añadir especialidades, usuarios, clientes, servicios, comentarios, etc.

## Aplicación

| Archivo |   Descripción                                                                                            |

| `js/hideHeader.js`  | Detecta el scroll y oculta o muestra la cabecera para ganar espacio de lectura.                        |
| `js/toggleMenu.js`  | Gestiona la apertura y cierre del menú lateral al pulsando el botón que sale.                          |
| `js/app.js`         | Donde se encuentra toda la aplicación web                                                              |      

Estos son los principales archivos de la aplicación que estarán en todas las páginas, o casi todas.

El usuario primeramente tendrá que ir a la página de registro para poder usar la aplicación.

Usaré en todos los 