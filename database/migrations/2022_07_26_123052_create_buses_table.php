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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('car_number');
            $table->point('location')->nullable();
            $table->double('speed')->nullable();
            $table->double('sc')->nullable();
            $table->integer('route_id')->nullable();
            $table->bigInteger('prev_stop_id')->nullable();
            $table->enum('side',['ahead','back'])->nullable();
            $table->boolean('status')->default(0)->nullable();
            $table->double('dir')->nullable();
            $table->string('imei')->nullable();
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
        Schema::dropIfExists('buses');
    }
};
