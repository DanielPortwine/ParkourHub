<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersMovementsBaselineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_movements_baseline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('movement_id');
            $table->unsignedBigInteger('movement_field_id');
            $table->integer('value');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('movement_id')->references('id')->on('movements')->onDelete('cascade');
            $table->foreign('movement_field_id')->references('id')->on('movements_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_movements_baseline');
    }
}
