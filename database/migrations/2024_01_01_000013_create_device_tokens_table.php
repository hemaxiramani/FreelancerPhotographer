<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_name', 100);
            $table->enum('device_type', ['android', 'ios', 'web']);
            $table->text('fcm_token')->nullable();
            $table->unsignedBigInteger('access_token_id')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('access_token_id');

            $table->foreign('access_token_id')
                  ->references('id')
                  ->on('personal_access_tokens')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
