<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkoutMovementsFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workout_movement_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movement_field_id');
            $table->unsignedBigInteger('workout_movement_id');
            $table->integer('value')->nullable();
            $table->timestamps();

            $table->foreign('movement_field_id')->references('id')->on('movement_fields')->onDelete('cascade');
            $table->foreign('workout_movement_id')->references('id')->on('workout_movements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workout_movement_fields');
    }
}
