<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->enum('payment_type', ['hourly', 'monthly_fixed']);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_contracts');
    }
};
