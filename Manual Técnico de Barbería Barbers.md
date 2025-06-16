# Manual Técnico de Barbería Barbers

**Versión:** 1.0  
**Autor:** Francisco  Miguel Rodríguez Bras / Seyte SL  
**Fecha:** 18 de junio de 2025

---
## Índice

1. [Introducción](#visión-general-del-sistema)  
2. [Tecnologías empleadas](#arquitectura)  
3. [Instalación y despliegue](#instalación-y-despliegue) 
4. [Estructura de carpetas](#requisitos-del-sistema) 
5. [Arranque de la aplicación](#detalles-de-diseño)  
6. [API](#api-y-servicios)  
---

## Visión general del sistema

La aplicación web se encarga de la gestión de citas por parte de los clientes, así como la interacción de los mismos con la reciprocidad del servicio recibido. En esta versión de la aplicación nos encontramos con unos pilares básicos pero con un alto grado de escalabilidad.
La aplicación trabaja con los siguientes bloques: usuarios, clientes, empleados, especialidades, contratos, servicios, 

---

## Tecnologías empleadas

- Backend: Laravel 11 (PHP 8.2)
- Base de datos: MySQL 8
- Servidor web: Nginx
- Contenedores: Docker & Docker Compose
- Frontend: HTML, CSS y JavaScript estático

## Instalación y despliegue
1. **Clonar repositorio**  
   ```bash
   git clone https://tu.repo/mi-aplicacion.git
   cd mi-aplicacion

## Estructura de carpetas
