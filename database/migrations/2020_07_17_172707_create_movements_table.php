<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('youtube')->nullable();
            $table->integer('youtube_start')->nullable();
            $table->string('video')->nullable();
            $table->string('video_type')->nullable();
            $table->boolean('official')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('movement_categories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movements');
    }
}
