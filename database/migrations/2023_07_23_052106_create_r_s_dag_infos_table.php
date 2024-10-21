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
        Schema::create('r_s_dag_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('sa_id');
            $table->string('rs_dag_no');
            $table->string('rs_khatiyan_no');
            $table->float('total_rs_area')->default(0.0);
            $table->float('total_rs_use_area')->default(0.0);
            $table->string('rs_porca_scan_copy')->nullable();
            $table->string('rs_dag_map_scan_copy')->nullable();
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
        Schema::dropIfExists('r_s_dag_infos');
    }
};
