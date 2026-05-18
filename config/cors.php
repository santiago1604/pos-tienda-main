<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) — StoreCell API
|--------------------------------------------------------------------------
|
| Controla qué dominios externos pueden consumir la API REST.
| Para producción cambia 'allowed_origins' al dominio real del frontend.
|
| Instalación del middleware (ya incluido en Laravel 12):
|   El middleware HandleCors se registra automáticamente.
|
*/

return [

    /*
     | Rutas a las que se aplica CORS.
     | 'api/*' cubre todos los endpoints bajo /api/.
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     | Métodos HTTP permitidos.
     */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
     | Orígenes permitidos.
     | En desarrollo se acepta cualquiera (*).
     | En producción especifica el dominio del cliente, p.ej.:
     |   'allowed_origins' => ['https://app.storecell.com'],
     */
    'allowed_origins' => ['*'],

    /*
     | Patrones de origen (alternativa a allowed_origins con wildcard).
     */
    'allowed_origins_patterns' => [],

    /*
     | Cabeceras que el cliente puede enviar.
     */
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
        'X-CSRF-TOKEN',
    ],

    /*
     | Cabeceras expuestas al cliente en la respuesta.
     */
    'exposed_headers' => [],

    /*
     | Tiempo (segundos) que el navegador puede cachear la respuesta preflight.
     */
    'max_age' => 3600,

    /*
     | Si se envían cookies / credenciales en las solicitudes.
     | Necesario para sesiones Laravel con Sanctum.
     */
    'supports_credentials' => true,

];
