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
        Schema::create('customer_land_price_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_land_info_id');
            $table->integer('total_amount')->default(0);
            $table->integer('land_price_per_decimal')->default(0);
            $table->integer('total_booking_money')->default(0);
            $table->date('booking_money_date')->nullable();
            $table->integer('booking_money_paid')->default(0);
            $table->integer('total_downpayment_amount')->default(0);
            $table->integer('downpayment_paid')->default(0);
            $table->integer('total_installment_amount')->default(0);
            $table->integer('total_installment_amount_paid')->default(0);
            $table->integer('no_of_installment')->default(0);
            $table->integer('per_month_installment')->default(0);
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
        Schema::dropIfExists('customer_land_price_infos');
    }
};
