<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\EmployeeSpecialtyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProfileController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('/', [PageController::class, 'index'])->name('index');
Route::get('/acerca-de', [PageController::class, 'acercaDe'])->name('acerca-de');
Route::get('/equipo', [PageController::class, 'equipo'])->name('equipo');
Route::get('/servicios', [PageController::class, 'servicios'])->name('servicios');
Route::get('/contacto', [PageController::class, 'contacto'])->name('contacto');
Route::get('/portada', [PageController::class, 'portada'])->name('portada');

//Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});


// Rutas registro y login
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
// Ruta logout con middleware
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
// Ruta para obtener los datos y un usuario autenticado
Route::get('getUser', [AuthController::class, 'getUser'])->middleware('auth:sanctum');

// Perfil Usuario
Route::post('profile', [ProfileController::class, 'add'])->middleware('auth:sanctum');
Route::put('profile', [ProfileController::class, 'update'])->middleware('auth:sanctum');

// Clientes
Route::post('customers', [CustomerController::class, 'add']);
Route::post('{id}/customers', [CustomerController::class, 'addClient']);
Route::get('customers', [CustomerController::class, 'show']);
Route::get('customers/{id}', [CustomerController::class, 'getCustomer']);
Route::put('customers/{id}', [CustomerController::class, 'update']);
Route::put('customers/{id}', [CustomerController::class, 'update']);
Route::delete('customers/{id}', [CustomerController::class, 'delete']);

// Rutas para gestionar contratos de un cliente específico
Route::post('customers/{id}/contracts', [ContractController::class, 'add']);
Route::get('contracts', [ContractController::class, 'showAll']);
Route::get('{id}/contracts', [ContractController::class, 'show']);
Route::get('customers/{id}/contracts/{contractId}', [ContractController::class, 'getContract']);
Route::put('contracts/{contractId}', [ContractController::class, 'update']);
Route::delete('contracts/{contractId}', [ContractController::class, 'delete']);

//Empleados
Route::post('employees', [EmployeeController::class, 'add']);
Route::post('{id}/employees', [EmployeeController::class, 'addEmployee']);
Route::get('employees', [EmployeeController::class, 'show']);
Route::get('employees/{id}', [EmployeeController::class, 'getEmployee']);
Route::put('employees/{id}', [EmployeeController::class, 'update']);
Route::delete('employees/{id}', [EmployeeController::class, 'delete']);

//Citas
Route::post('appointments', [AppointmentController::class, 'add']);
/**
 *      nombre_cliente y nombre_empleado: string
 *      DNI_cliente y DNI_empleado: string con ocho digitos y una letra
 *      fecha: datetime
 *      estado: enum ('pendiente', 'cancelado', 'completo')
 *      skip: el numero de lineas que se saltara la query, por ejemplo skip(5) no ensena las primeras 5 lineas de la tabla
 *      take: el numero de lineas que ensenara/limitara la query, por ejemplo take(5) solo ensena 5 lineas
 *      withServicios: un booleano falso, escribe un string "true" para enseñar los servicios asociados a su cita, cualquier otra cosa para no hacerlo 
 * 
 * Se manda con parametros, que toman forma de por ejemplo "appointments?nombreCliente=Rafa&nombre_empleado:Alicia"
 */
Route::get('appointments',[AppointmentController::class, 'show']);
Route::get('appointments/{id}/{withServicios}', [AppointmentController::class, 'getAppointment']);
Route::put('appointments/{id}', [AppointmentController::class, 'update']);
Route::delete('appointments/{id}', [AppointmentController::class, 'delete']);

//Servicios
Route::post('services', [ServiceController::class, 'add']);
Route::get('services', [ServiceController::class, 'show']);
Route::get('services/{id}', [ServiceController::class, 'getService']);
Route::put('services/{id}', [ServiceController::class, 'update']);
Route::delete('services/{id}', [ServiceController::class, 'delete']);

// Especialidades
Route::post('specialties', [SpecialtyController::class, 'add']);
Route::get('specialties', [SpecialtyController::class, 'show']);
Route::get('specialties/{id}', [SpecialtyController::class, 'getSpecialty']);
Route::put('specialties/{id}', [SpecialtyController::class, 'update']);
Route::delete('specialties/{id}', [SpecialtyController::class, 'delete']);

// Rutas para asignar especialidades a empleados
Route::post('employees/{employee}/specialties', [EmployeeSpecialtyController::class, 'assign']);
Route::get('employees/{employee}/specialties', [EmployeeSpecialtyController::class, 'list']);
Route::delete('employees/{employee}/specialties/{specialty}', [EmployeeSpecialtyController::class, 'delete']);
