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
        Schema::create('land_seller_agreements', function (Blueprint $table) {
            $table->id();
            $table->date('agreement_date');
            $table->float('land_size_katha');
            $table->string('purchase_deed_no');
            $table->integer('dolil_variation_id');
            $table->date('sub_deed_date');
            $table->string('sub_deed_no');
            $table->integer('landBrokerID')->nullable();
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
        Schema::dropIfExists('land_seller_agreements');
    }
};
