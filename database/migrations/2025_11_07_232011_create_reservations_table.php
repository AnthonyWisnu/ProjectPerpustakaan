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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reservation_code', 50)->unique();
            $table->enum('status', ['pending', 'ready', 'picked_up', 'cancelled', 'expired'])->default('pending');
            $table->integer('total_books')->default(1);
            $table->timestamp('reserved_at')->useCurrent();
            $table->timestamp('expired_at');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('reservation_code');
            $table->index('status');
            $table->index('expired_at');

            // Composite index for member's active reservations query
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
