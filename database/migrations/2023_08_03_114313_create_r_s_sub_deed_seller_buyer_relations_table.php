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
        Schema::create('r_s_sub_deed_seller_buyer_relations', function (Blueprint $table) {
            $table->id();
            $table->integer('rs_sub_deed_id');
            $table->integer('rs_seller_buyer_id');
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
        Schema::dropIfExists('r_s_sub_deed_seller_buyer_relations');
    }
};
