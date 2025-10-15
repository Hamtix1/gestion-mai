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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
            $table->string('concept'); // Concepto del ingreso
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('source', ['sterilization', 'donation', 'fundraising', 'other'])->default('other');
            $table->date('income_date');
            $table->string('reference_number')->nullable();
            $table->foreignId('registered_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('income_date');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
