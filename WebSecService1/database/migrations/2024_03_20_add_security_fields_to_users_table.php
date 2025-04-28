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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_password_change')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_login')->nullable();
            $table->boolean('is_locked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_password_change');
            $table->dropColumn('failed_login_attempts');
            $table->dropColumn('last_failed_login');
            $table->dropColumn('is_locked');
        });
    }
}; 