<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spot_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('difficulty', ['1', '2', '3', '4', '5']);
            $table->enum('visibility', ['private', 'follower', 'public'])->default('private');
            $table->string('video')->nullable();
            $table->string('video_type')->nullable();
            $table->string('youtube')->nullable();
            $table->integer('youtube_start')->nullable();
            $table->string('thumbnail');
            $table->boolean('won')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('spot_id')->references('id')->on('spots')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenges');
    }
}
