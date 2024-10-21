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
        Schema::create('s_a_dag_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('cs_id');
            $table->string('sa_dag_no');
            $table->string('sa_khatiyan_no');
            $table->float('total_sa_area')->default(0.0);
            $table->float('total_sa_use_area')->default(0.0);
            $table->string('sa_porca_scan_copy')->nullable();
            $table->string('sa_dag_map_scan_copy')->nullable();
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
        Schema::dropIfExists('s_a_dag_infos');
    }
};
