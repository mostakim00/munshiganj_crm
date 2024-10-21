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
        Schema::create('r_s_mouza_map_info_images', function (Blueprint $table) {
            $table->id();
            $table->integer('rs_mouza_map_id');
            $table->string('file_name')->nullable();      
            $table->string('file_path')->nullable();
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
        Schema::dropIfExists('r_s_mouza_map_info_images');
    }
};
