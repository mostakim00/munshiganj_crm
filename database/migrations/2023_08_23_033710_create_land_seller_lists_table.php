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
        Schema::create('land_seller_lists', function (Blueprint $table) {
            $table->id();
            $table->date('join_date')->nullable();
            $table->string('name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('email')->nullable();
            $table->string('nid');
            $table->date('dob');
            $table->string('mobile_no_1');
            $table->string('mobile_no_2')->nullable();
            $table->string('present_address');
            $table->string('permanent_address');
            $table->string('image');
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
        Schema::dropIfExists('land_seller_lists');
    }
};
