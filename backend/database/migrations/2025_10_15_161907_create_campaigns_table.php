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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location'); // Ubicación de la campaña
            $table->integer('available_slots')->default(0); // Cupos disponibles
            $table->integer('used_slots')->default(0); // Cupos utilizados
            $table->decimal('dog_price', 10, 2); // Precio para perros
            $table->decimal('cat_price', 10, 2); // Precio para gatos
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->boolean('is_visible_to_public')->default(true); // Visible para visitantes
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
