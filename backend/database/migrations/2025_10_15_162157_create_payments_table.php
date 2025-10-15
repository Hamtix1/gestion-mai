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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sterilization_id')->constrained('sterilizations')->onDelete('restrict');
            $table->decimal('amount', 10, 2); // Monto del pago/abono
            $table->enum('payment_method', ['cash', 'transfer', 'card', 'other'])->default('cash');
            $table->string('reference_number')->nullable(); // Número de referencia/transacción
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users')->onDelete('restrict'); // Usuario que recibió el pago
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('sterilization_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
