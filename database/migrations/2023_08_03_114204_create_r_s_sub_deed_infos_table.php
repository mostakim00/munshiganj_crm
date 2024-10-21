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
        Schema::create('r_s_sub_deed_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('rs_id');
            $table->date('sub_deed_date');
            $table->string('sub_deed_no');
            $table->float('sub_deed_land_size')->default(0.0);
            $table->string('sub_deed_registry_office');
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('r_s_sub_deed_infos');
    }
};
