<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;

/**
 * Controlador base para la capa REST.
 * Todos los ApiControllers extienden este clase para heredar
 * los helpers de respuesta estandarizada (ApiResponse trait).
 */
abstract class ApiController extends Controller
{
    use ApiResponse;
}
