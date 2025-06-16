<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Clase Controlador en la que incluye
 * Las paginas estaticas de la barberia
 */
class PageController extends Controller
{
    public function index() {
        return view('welcome');
    }
    
    public function acercaDe() {
        return view('acerca-de');
    }

    public function equipo() {
        return view('equipo');
    }

    public function servicios() {
        return view('servicios');
    }

    public function contacto() {
        return view('contacto');
    }

    public function portada() {
        return view('portada');
    }
}
