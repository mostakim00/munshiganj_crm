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
        Schema::create('b_s_dag_recorded_person_relations', function (Blueprint $table) {
            $table->id();
            $table->integer('BSDagInfoId');
            $table->integer('BSDagRecordedPeopleId');
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
        Schema::dropIfExists('b_s_dag_recorded_person_relations');
    }
};
