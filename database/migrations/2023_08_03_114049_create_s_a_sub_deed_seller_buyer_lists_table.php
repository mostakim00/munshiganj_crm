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
        Schema::create('s_a_sub_deed_seller_buyer_lists', function (Blueprint $table) {
            $table->id();
            $table->string('seller_name');
            $table->string('seller_father_name');
            $table->string('buyer_name');
            $table->string('buyer_father_name');
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
        Schema::dropIfExists('s_a_sub_deed_seller_buyer_lists');
    }
};
