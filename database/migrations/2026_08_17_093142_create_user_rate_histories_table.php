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
        Schema::create('user_rate_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_type_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('effective_from')->index();
            $table->date('effective_until')->nullable()->index();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'rate_type_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rate_histories');
    }
};
