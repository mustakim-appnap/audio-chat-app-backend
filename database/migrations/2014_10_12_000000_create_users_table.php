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
        if (! (env('APP_ENV') == 'local')) {
            \Illuminate\Support\Facades\DB::statement('SET SESSION sql_generate_invisible_primary_key=0');
        }
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('auth_id', 255)->nullable();
            $table->string('device_token', 255);
            $table->string('username', 50)->nullable();
            $table->date('dob')->nullable();
            $table->string('socket_id')->unique()->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->tinyInteger('is_active')->default(\Illuminate\Support\Facades\Config::get('variable_constants.check.no'));
            $table->tinyInteger('is_premium')->default(\Illuminate\Support\Facades\Config::get('variable_constants.check.no'));
            $table->tinyInteger('status')->default(\Illuminate\Support\Facades\Config::get('variable_constants.activation.active'));
            $table->tinyInteger('is_guest_user')->default(\Illuminate\Support\Facades\Config::get('variable_constants.check.no'));
            $table->tinyInteger('is_registration_complete')->default(\Illuminate\Support\Facades\Config::get('variable_constants.check.no'));
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
