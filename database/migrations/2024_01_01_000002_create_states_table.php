<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('state_code', 10)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
