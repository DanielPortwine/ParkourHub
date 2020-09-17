<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpotsWorkoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spots_workouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spot_id');
            $table->unsignedBigInteger('workout_id');
            $table->timestamps();

            $table->foreign('spot_id')->references('id')->on('spots')->onDelete('cascade');
            $table->foreign('workout_id')->references('id')->on('workouts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spots_workouts');
    }
}
