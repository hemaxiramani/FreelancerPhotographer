<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete();
            $table->string('name', 150);
            $table->boolean('is_user_added')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('state_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
