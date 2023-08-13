<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title_ru_short')->nullable();
            $table->string('title_en_short')->nullable();
            $table->string('title_tk_short')->nullable();
            $table->string('title_ru')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_tk')->nullable();
            $table->string('body_ru_short')->nullable();
            $table->string('body_tk_short')->nullable();
            $table->string('body_en_short')->nullable();
            $table->text('body_ru')->nullable();
            $table->text('body_tk')->nullable();
            $table->text('body_en')->nullable();
            $table->boolean('status')->default(0)->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
