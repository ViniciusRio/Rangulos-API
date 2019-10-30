<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('title');
            $table->string('about');
            $table->string('address');
            $table->double('price', 8, 2);
            $table->integer('max_guests');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('url_image')->nullable();
            $table->integer('user_creator_id')->unsigned()->nullable();
            $table->foreign('user_creator_id')->references('id')->on('users');
            $table->timestamps();
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
