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
        Schema::create('posting_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->date('payment_date')->index();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable()->index();
            $table->text('remarks')->nullable();
            $table->string('status')->default('posted')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posting_payments');
    }
};
