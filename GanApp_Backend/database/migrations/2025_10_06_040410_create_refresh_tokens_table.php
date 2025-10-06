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
        Schema::create('refresh_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64);                 // hash('sha256', token)
            $t->string('client_ip', 45)->nullable();
            $t->string('client_ua', 255)->nullable();
            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();         // null = usable; set -> rotado/invalidado
            $t->timestamps();
            $t->index(['user_id', 'token_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
