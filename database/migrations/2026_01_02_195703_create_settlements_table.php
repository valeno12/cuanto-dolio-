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
        Schema::create('settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignUuid('from_participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignUuid('to_participant_id')->constrained('participants')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method')->nullable(); // 'cash', 'transfer'
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'is_paid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
