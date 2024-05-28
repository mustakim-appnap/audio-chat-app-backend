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
        Schema::create('private_channel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('private_channel_id')->constrained('private_channels');
            $table->foreignId('user_id')->constrained('users');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_channel_members');
    }
};
