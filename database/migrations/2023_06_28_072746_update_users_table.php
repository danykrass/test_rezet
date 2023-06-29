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
            $table->renameColumn('name', 'first_name');
            $table->string('last_name')->after('name')->nullable();
            $table->string('email')->after('last_name')->change();
            $table->string('profile')->nullable()->after('email');
            $table->enum('status', ['Active', 'Deactive'])->default('Deactive')->after('profile');
            $table->dropColumn('remember_token');
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->dropColumn('last_name');
            $table->dropColumn('profile');
            $table->dropColumn('email');
            $table->string('email')->unique();
            $table->dropColumn('status');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
