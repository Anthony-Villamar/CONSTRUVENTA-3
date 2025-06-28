<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID como PK
            $table->string('cedula_cliente', 15);
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->string('direccion_entrega', 100);
            $table->string('zona_entrega', 50);
            $table->timestamps();

            // Relación con clientes (aunque está en otro microservicio)
            // Este sería un FK lógico, no físico aquí.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
