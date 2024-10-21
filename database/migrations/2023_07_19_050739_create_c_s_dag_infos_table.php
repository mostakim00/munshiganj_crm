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
        Schema::create('c_s_dag_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('mouza_id');
            $table->string('cs_dag_no');
            $table->string('cs_khatiyan_no');         
            $table->float('total_cs_area')->default(0.0);
            $table->float('total_cs_use_area')->default(0.0);
            $table->string('cs_porca_scan_copy')->nullable(); 
            $table->string('cs_dag_map_scan_copy')->nullable();
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
        Schema::dropIfExists('c_s_dag_infos');
    }
};
