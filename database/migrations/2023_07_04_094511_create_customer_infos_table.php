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
        Schema::create('customer_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('spouse_name')->nullable();
            $table->string('spouse_type')->nullable();
            $table->string('nid');
            $table->date('dob');
            $table->string('mobile_number_1');
            $table->string('mobile_number_2')->nullable(); 
            $table->string('other_file_no')->nullable(); 
            $table->string('profession')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('mailing_address')->nullable();
            $table->string('permanent_address');
            $table->string('office_address')->nullable();
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
        Schema::dropIfExists('customer_infos');
    }
};
