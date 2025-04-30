<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Ruta de ejemplo para la API
Route::get('register', [AuthController::class, 'register']);

// Otra ruta de ejemplo
Route::middleware('api')->post('/data', function (Request $request) {
    return response()->json(['received' => $request->all()]);
});

