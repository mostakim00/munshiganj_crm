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
        Schema::create('agent_payment_information', function (Blueprint $table) {
            $table->id();
            $table->string("customer_land_info_id");
            $table->integer('agent_money')->default(0);
            $table->integer('agent_money_paid')->default(0);
            $table->string('agent_money_note')->nullable();
            $table->integer('extra_money')->default(0);
            $table->integer('extra_money_paid')->default(0);
            $table->string('extra_money_note')->nullable();
            $table->integer('agent_conveyance')->default(0);
            $table->integer('agent_conveyance_paid')->default(0);
            $table->string('agent_conveyance_note')->nullable();
            $table->integer('mobile_bill')->default(0);
            $table->integer('mobile_bill_paid')->default(0);
            $table->string('mobile_bill_note')->nullable();
            $table->integer('entertainment')->default(0);
            $table->integer('entertainment_paid')->default(0);
            $table->string('entertainment_note')->nullable();
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
        Schema::dropIfExists('agent_payment_information');
    }
};
