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
        Schema::create('user_likes', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');       // who liked
    $table->unsignedBigInteger('liked_user_id'); // who got liked
    $table->timestamps();

    $table->unique(['user_id', 'liked_user_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_likes');
    }
};
