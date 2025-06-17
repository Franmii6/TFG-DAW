<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\EmployeeSpecialtyController;
use App\Http\Controllers\ComentarioController;


// ----------------------
// Rutas “públicas”
// ----------------------
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::get('services',    [ServiceController::class, 'show']);
Route::get('employees',   [EmployeeController::class, 'show']);
// Mostrar los últimos 5 comentarios.
Route::get('comentarios/recientes', [ComentarioController::class, 'mostrarComentarios']);


// ----------------------
// Rutas protegidas (Auth)
// ----------------------
Route::middleware('auth:sanctum')->group(function () {
    // Usuarios
    Route::get('getUser', [AuthController::class, 'getUser']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('usuarios',[AuthController::class, 'showAll']);
    Route::delete('usuarios/{idUsuario}', [AuthController::class, 'deleteUser']);

    // Perfil
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile', [ProfileController::class, 'add']);
    Route::put('profile', [ProfileController::class, 'update']);

    // Clientes
    Route::post('customers',       [CustomerController::class, 'add']);
    Route::post('{id}/customers',  [CustomerController::class, 'addClient']);
    Route::get('customers',        [CustomerController::class, 'show']);
    Route::get('customers/{id}',   [CustomerController::class, 'getCustomer']);
    Route::put('customers/{id}',   [CustomerController::class, 'update']);
    Route::delete('customers/{id}',[CustomerController::class, 'delete']);

    // Servicios
    Route::post('services',        [ServiceController::class, 'add']);
    Route::get('services/{id}',    [ServiceController::class, 'getService']);
    Route::put('services/{id}',    [ServiceController::class, 'update']);
    Route::delete('services/{id}', [ServiceController::class, 'delete']);

    // Empleados
    Route::get('employees/{id}',   [EmployeeController::class, 'getEmployee']);

    // Citas
    Route::post('appointments',                          [AppointmentController::class, 'add']);
    Route::get('appointments',                           [AppointmentController::class, 'show']);
    Route::get('appointments/{id}/{withServicios}',      [AppointmentController::class, 'getAppointment'])
            ->where('withServicios', 'servicios'); //Esto especifica que se refiere a servicios, porque interfería con la ruta id/comentarios
    Route::put('appointments/{id}',                      [AppointmentController::class, 'update']);
    Route::delete('appointments/{id}',                   [AppointmentController::class, 'delete']);

    // Listar citas de un cliente (por su ID)
    Route::get('customers/{id}/appointments',            [AppointmentController::class, 'indexPorCliente']);

    // Contratos
    Route::post('customers/{id}/contracts',              [ContractController::class, 'add']);
    Route::get('contracts',                              [ContractController::class, 'showAll']);
    Route::get('contracts/{id}',                         [ContractController::class, 'show']);
    Route::get('customers/{id}/contracts/{contractId}',  [ContractController::class, 'getContract']);

    // Especialidades
    Route::post('specialties',       [SpecialtyController::class, 'add']);
    Route::get('specialties',        [SpecialtyController::class, 'show']);
    Route::get('specialties/{id}',   [SpecialtyController::class, 'getSpecialty']);
    Route::put('specialties/{id}',   [SpecialtyController::class, 'update']);
    Route::delete('specialties/{id}',[SpecialtyController::class, 'delete']);

    // Asignar / listar / eliminar especialidades de un empleado
    Route::post('employees/{employee}/specialties',                   [EmployeeSpecialtyController::class, 'assign']);
    Route::get('employees/{employee}/specialties',                    [EmployeeSpecialtyController::class, 'list']);
    Route::delete('employees/{employee}/specialties/{specialty}',     [EmployeeSpecialtyController::class, 'delete']);

    // ----------------------
    // Rutas para Comentarios
    // ----------------------
    // 1) Añadir un comentario a una cita
    Route::post('appointments/{cita}/comentarios',        [ComentarioController::class, 'add']);

    // 2) Actualizar el comentario de una cita existente
    Route::put('appointments/{cita}/comentarios',         [ComentarioController::class, 'update']);

    // 3) Mostrar el comentario específico de una cita
    Route::get('appointments/{cita}/comentarios',         [ComentarioController::class, 'showEspecific']);

    // 4) Eliminar el comentario asociado a una cita
    Route::delete('appointments/{cita}/comentarios',      [ComentarioController::class, 'delete']);

    // 5) Listar todos los comentarios de un cliente (por su ID de cliente)
    Route::get('customers/{id}/comentarios',              [ComentarioController::class, 'showPorCliente']);
    
    //Ruta para ver los slots de disponibilidad del empleado por servicio
    Route::get('/services/{servicio}/available-slots', [AppointmentController::class, 'disponibilidadSlotsServicio']);
});
