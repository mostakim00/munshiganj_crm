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
        Schema::create('land_brokers', function (Blueprint $table) {
            $table->id();
            $table->date('joining_date')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('nid');
            $table->date('dob')->nullable();
            $table->string('educational_background')->nullable();
            $table->string('mobile_number_1');
            $table->string('mobile_number_2')->nullable();
            $table->string('email')->nullable();
            $table->string('present_address')->nullable();
            $table->string('permanent_address')->nullable();
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
        Schema::dropIfExists('land_brokers');
    }
};
