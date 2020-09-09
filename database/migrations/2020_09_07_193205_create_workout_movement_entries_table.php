<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkoutMovementEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workout_movement_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('movement_id');
            $table->unsignedBigInteger('workout_entry_id');
            $table->integer('reps')->nullable();
            $table->integer('weight')->nullable(); // grams
            $table->integer('duration')->nullable(); // seconds
            $table->integer('distance')->nullable(); // centimetres
            $table->integer('height')->nullable(); // centimetres
            $table->enum('feeling', [1, 2, 3, 4, 5])->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('movement_id')->references('id')->on('movements')->onDelete('cascade');
            $table->foreign('workout_entry_id')->references('id')->on('workout_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workout_movement_entries');
    }
}
