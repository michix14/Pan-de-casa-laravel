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
    Schema::create('productos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre', 100);
        $table->string('descripcion', 100);
        $table->decimal('precio', 10, 2);
        $table->integer('stock');
        $table->string('imagen_url', 255)->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
