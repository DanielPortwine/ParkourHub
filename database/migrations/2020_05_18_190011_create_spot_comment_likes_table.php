<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpotCommentLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spot_comment_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('spot_comment_id')->references('id')->on('spot_comments');
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
        Schema::dropIfExists('spot_comment_likes');
    }
}
