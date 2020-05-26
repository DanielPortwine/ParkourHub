<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengeEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challenge_id');
            $table->unsignedBigInteger('user_id');
            $table->string('youtube')->nullable();
            $table->integer('youtube_start')->nullable();
            $table->string('video')->nullable();
            $table->string('video_type')->nullable();
            $table->boolean('winner')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('challenge_id')->references('id')->on('challenges');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_entries');
    }
}
