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
            $table->enum('gender', ['femal','male','unknow'])->after('address')->nullable();
            $table->date('birthdate')->after('gender')->nullable();
            $table->string('profile_picture')->after('birthdate')->nullable();
            $table->enum('user_type', ['system', 'member'])->default('system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
};
