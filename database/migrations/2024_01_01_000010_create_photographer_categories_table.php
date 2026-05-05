<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->decimal('charge_per_day', 10, 2)->nullable();

            $table->unique(['photographer_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_categories');
    }
};
