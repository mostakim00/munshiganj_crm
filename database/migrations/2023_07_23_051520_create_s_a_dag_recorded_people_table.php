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
        Schema::create('s_a_dag_recorded_people', function (Blueprint $table) {
            $table->id();
            $table->string('sa_recorded_person');
            $table->string('sa_recorded_person_fathers_name');
            $table->float('sa_recorded_person_ownership_size')->default(0);
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
        Schema::dropIfExists('s_a_dag_recorded_people');
    }
};
