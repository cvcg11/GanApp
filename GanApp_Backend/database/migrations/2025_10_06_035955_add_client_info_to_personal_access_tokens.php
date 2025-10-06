<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $t) {
            $t->string('client_ip', 45)->nullable()->after('abilities');
            $t->string('client_ua', 255)->nullable()->after('client_ip');
            $t->index(['client_ip', 'client_ua']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $t) {
            $t->dropIndex(['client_ip', 'client_ua']);
            $t->dropColumn(['client_ip', 'client_ua']);
        });
    }
};
