<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('date_time');
            $table->enum('visibility', ['private', 'follower', 'public'])->default('private');
            $table->boolean('link_access')->default(false);
            $table->enum('accept_method', ['none', 'invite', 'accept'])->default('none');
            $table->string('video')->nullable();
            $table->string('video_type')->nullable();
            $table->string('youtube')->nullable();
            $table->integer('youtube_start')->nullable();
            $table->string('thumbnail');
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('events');
    }
}
