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
        Schema::create('customer_payment_statement_installments', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_land_price_infos_id');
            $table->integer('installment_id');
            $table->date('payment_date');
            $table->string('payment_against');
            $table->string('paid_by');
            $table->string('mobile_banking_account_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_cheque_no')->nullable();
            $table->string('money_receipt_no')->nullable();
            $table->integer('amount')->default(0);
            $table->string('document_image')->nullable();
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
        Schema::dropIfExists('customer_payment_statement_installments');
    }
};
