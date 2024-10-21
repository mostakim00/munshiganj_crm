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
        Schema::create('customer_installments', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_land_price_infos_id');
            $table->integer('amount')->default(0);
            $table->integer('paid')->default(0);
            $table->string('installment_no')->nullable();
            $table->string('start_date')->nullable();
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
        Schema::dropIfExists('customer_installments');
    }
};
