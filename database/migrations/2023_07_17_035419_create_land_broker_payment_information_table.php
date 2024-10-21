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
        Schema::create('land_broker_payment_information', function (Blueprint $table) {
            $table->id();
            $table->string("land_seller_agreements_id");
            $table->string("broker_money")->default(0);
            $table->string("broker_money_paid")->default(0);
            $table->string("broker_money_note")->nullable();
            $table->string("extra_money")->default(0);
            $table->string("extra_money_paid")->default(0);
            $table->string("extra_money_note")->nullable();
            $table->string("conveyance_money")->default(0);
            $table->string("conveyance_money_paid")->default(0);
            $table->string("conveyance_money_note")->nullable();
            $table->string("mobile_bill")->default(0);
            $table->string("mobile_bill_paid")->default(0);
            $table->string("mobile_bill_note")->nullable();
            $table->string("entertainment")->default(0);
            $table->string("entertainment_paid")->default(0);
            $table->string("entertainment_note")->nullable();
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
        Schema::dropIfExists('land_broker_payment_information');
    }
};
