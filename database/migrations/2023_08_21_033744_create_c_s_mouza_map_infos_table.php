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
        Schema::create('c_s_mouza_map_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('cs_id');
            $table->float('land_size_sotangsho');
            $table->float('land_size_ojutangsho');
            $table->float('land_size_sq_feet');
            $table->float('land_eastTowest_sq_feet');
            $table->float('land_northToSouth_sq_feet');
            $table->integer('land_eastAndSouth_angle');
            $table->integer('land_eastAndNorth_angle');
            $table->integer('land_westAndSouth_angle');
            $table->integer('land_westAndNorth_angle');
            $table->float('eastSouth_to_westNorth_length');
            $table->float('southWest_to_northEast_length');
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
        Schema::dropIfExists('c_s_mouza_map_infos');
    }
};
