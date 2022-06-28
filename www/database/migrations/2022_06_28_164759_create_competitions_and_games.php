<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained();
        });
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('is_ai');
            $table->string('piece');
            $table->integer('score');
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
        });
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('status');
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
        });
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('index');
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
        });
        Schema::create('tiles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('x');
            $table->integer('y');
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('move_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tiles');
        Schema::dropIfExists('moves');
        Schema::dropIfExists('games');
        Schema::dropIfExists('players');
        Schema::dropIfExists('competitions');
    }
};
