<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migración: agregar customer_email a la tabla repairs
|--------------------------------------------------------------------------
| Permite enviar notificaciones por correo al cliente cuando su reparación
| está lista para retirar.
| Ejecutar: php artisan migrate
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropColumn('customer_email');
        });
    }
};
