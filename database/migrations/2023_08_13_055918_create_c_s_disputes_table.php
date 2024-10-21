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
        Schema::create('c_s_disputes', function (Blueprint $table) {
            $table->id();
            $table->integer('cs_id');
            $table->date('dispute_date');
            $table->string('dispute_no');
            $table->float('dispute_land_size');
            $table->string('dispute_court_name');
            $table->date('dispute_judgement_date');
            $table->string('case_badi_name');
            $table->string('case_bibadi_name');
            $table->longText('judgement_description')->nullable();
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
        Schema::dropIfExists('c_s_disputes');
    }
};
