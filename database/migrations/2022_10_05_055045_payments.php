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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('tarif_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->text('order_id')->nullable();
            $table->text('form_url')->nullable();
            $table->text('check_response_body')->nullable();
            $table->enum('payment_status',['success','fail','pending']);
            $table->boolean('expired');
            $table->text('error_message')->nullable();
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
