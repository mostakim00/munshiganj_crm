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
        Schema::create('customer_land_information', function (Blueprint $table) {
            $table->id();
            $table->integer('plot_id');
            $table->date('booking_date')->nullable();
            $table->date('expected_reg_date')->nullable();
            $table->string('plot_address_description')->nullable();
            $table->integer('agentID')->nullable();
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
        Schema::dropIfExists('customer_land_information');
    }
};
