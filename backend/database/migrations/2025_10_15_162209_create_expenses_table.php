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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
            $table->string('concept'); // Concepto del gasto
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('category', ['medical', 'transportation', 'supplies', 'marketing', 'administrative', 'other'])->default('other');
            $table->date('expense_date');
            $table->string('invoice_number')->nullable();
            $table->string('supplier')->nullable(); // Proveedor
            $table->foreignId('registered_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('expense_date');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
