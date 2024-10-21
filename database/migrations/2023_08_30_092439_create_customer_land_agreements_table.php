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
        Schema::create('customer_land_agreements', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_land_information_id');
            $table->integer('land_seller_agreement_id');
            $table->date('agreement_date');
            $table->float('land_size_katha');
            $table->float('land_size_ojutangsho');
            $table->string('deed_no');
            $table->date('registry_complete_date');
            $table->string('registry_sub_deed_no');
            $table->string('registry_office')->nullable();
            $table->integer('dolil_variation_id');
            $table->string('namejari_no');
            $table->date('namejari_date');
            $table->longText('description');
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
        Schema::dropIfExists('customer_land_agreements');
    }
};
