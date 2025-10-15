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
        Schema::create('sterilizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('restrict');
            
            // Datos del propietario
            $table->string('owner_full_name');
            $table->string('owner_id_number'); // Cédula
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->text('owner_address')->nullable();
            
            // Datos de la mascota
            $table->string('pet_name');
            $table->enum('pet_type', ['dog', 'cat']); // Perro o gato
            $table->enum('pet_gender', ['male', 'female']);
            $table->string('pet_breed')->nullable();
            $table->integer('pet_age_months')->nullable();
            $table->decimal('pet_weight', 5, 2)->nullable();
            
            // Datos financieros
            $table->decimal('total_price', 10, 2); // Precio total
            $table->decimal('total_paid', 10, 2)->default(0); // Total pagado
            $table->decimal('balance', 10, 2); // Saldo pendiente
            $table->enum('payment_status', ['pending', 'partial', 'completed'])->default('pending');
            
            // Datos de la esterilización
            $table->date('scheduled_date')->nullable(); // Fecha agendada
            $table->time('scheduled_time')->nullable(); // Hora agendada
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            
            // Auditoría
            $table->foreignId('registered_by')->constrained('users')->onDelete('restrict'); // Vendedor que registró
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('owner_id_number');
            $table->index('scheduled_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sterilizations');
    }
};
