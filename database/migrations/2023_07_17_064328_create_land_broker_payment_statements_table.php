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
        Schema::create('land_broker_payment_statements', function (Blueprint $table) {
            $table->id();
            $table->string('land_broker_payment_information_id');
            $table->string('payment_purpose');
            $table->integer('amount')->default(0);
            $table->string('payment_by');
            $table->string('mobile_account_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_cheque_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->longtext('note')->nullable();
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
        Schema::dropIfExists('land_broker_payment_statements');
    }
};
