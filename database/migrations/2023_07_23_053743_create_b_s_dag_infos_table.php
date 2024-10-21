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
        Schema::create('b_s_dag_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('rs_id');
            $table->string('bs_dag_no');
            $table->string('bs_khatiyan_no');
            $table->float('total_bs_area')->default(0.0);
            $table->float('total_bs_use_area')->default(0.0);
            $table->string('bs_porca_scan_copy')->nullable();
            $table->string('bs_dag_map_scan_copy')->nullable();
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
        Schema::dropIfExists('b_s_dag_infos');
    }
};
