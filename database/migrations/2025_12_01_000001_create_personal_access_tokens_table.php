<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migración: personal_access_tokens (Laravel Sanctum)
|--------------------------------------------------------------------------
|
| Sanctum emite tokens opacos (Bearer tokens) que se almacenan
| hasheados en esta tabla. Cada token representa una sesión de API
| independiente — equivalente funcional a JWT pero sin estado compartido.
|
| Para instalar Sanctum ejecutar:
|   composer require laravel/sanctum
|   php artisan migrate
|
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');                   // relación polimórfica con User
            $table->string('name');                        // nombre del token (p.ej. 'api-login')
            $table->string('token', 64)->unique();         // hash SHA-256 del token real
            $table->text('abilities')->nullable();         // scopes / permisos (JSON)
            $table->timestamp('last_used_at')->nullable(); // última vez que se usó
            $table->timestamp('expires_at')->nullable();   // expiración opcional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
