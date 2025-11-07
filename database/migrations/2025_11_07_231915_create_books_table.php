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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->unique()->nullable();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('cover_image')->nullable();
            $table->text('synopsis')->nullable();
            $table->integer('total_stock')->default(1);
            $table->integer('available_stock')->default(1);
            $table->string('shelf_location', 50)->nullable();
            $table->string('barcode', 100)->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('isbn');
            $table->index('title');
            $table->index('author');
            $table->index('available_stock');

            // Composite index for search optimization
            $table->index(['title', 'author']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
