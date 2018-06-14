<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCelestialBodiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('celestial_bodies', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('right_ascension');
            $table->integer('declination');
            $table->string('name')->nullable();
            $table->boolean('verified')->default(false);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('celestial_bodies');
    }
}
