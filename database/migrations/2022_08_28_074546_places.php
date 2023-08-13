<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->point('location')->nullable();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->text('images')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->integer('place_category_id')->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('mo')->nullable();
            $table->boolean('tu')->nullable();
            $table->boolean('we')->nullable();
            $table->boolean('th')->nullable();
            $table->boolean('fr')->nullable();
            $table->boolean('sa')->nullable();
            $table->boolean('su')->nullable();
            $table->string('time')->nullable();
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
        //
    }
};
