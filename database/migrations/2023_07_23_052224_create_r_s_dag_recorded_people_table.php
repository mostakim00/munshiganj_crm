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
        Schema::create('r_s_dag_recorded_people', function (Blueprint $table) {
            $table->id();
            $table->string('rs_recorded_person');
            $table->string('rs_recorded_person_fathers_name');
            $table->float('rs_recorded_person_ownership_size')->default(0);
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
        Schema::dropIfExists('r_s_dag_recorded_people');
    }
};
