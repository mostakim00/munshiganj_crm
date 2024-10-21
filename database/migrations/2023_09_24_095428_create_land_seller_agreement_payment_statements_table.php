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
        Schema::create('land_seller_agreement_payment_statements', function (Blueprint $table) {
            $table->id();
            $table->integer('land_seller_agreement_price_information_id');
            $table->integer('amount')->default(0);
            $table->string('amount_in_words')->nullable();
            $table->string('pay_by');
            $table->string('mobile_banking_account_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_cheque_no')->nullable();
            $table->date('next_payment_date');
            $table->string('payment_receiver_name');
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('land_seller_agreement_payment_statements');
    }
};
