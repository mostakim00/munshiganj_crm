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
        Schema::create('land_seller_agreement_price_information', function (Blueprint $table) {
            $table->id();
            $table->integer('land_seller_agreements_id');
            $table->integer('price_per_katha')->default(0);
            $table->integer('total_price')->default(0);
            $table->integer('paid_amount')->default(0);
            $table->date('payment_start_date');
            $table->date('approx_payment_complete_date');
            $table->date('approx_registry_date');
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
        Schema::dropIfExists('land_seller_agreement_price_information');
    }
};
