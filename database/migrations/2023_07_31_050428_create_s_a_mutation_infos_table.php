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
        Schema::create('s_a_mutation_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('sa_id');
            $table->date('mutation_date');
            $table->string('mutation_no');
            $table->float('land_size')->default(0.0);
            $table->string('owner_name');
            $table->string('ref_deed_no')->nullable();
            $table->date('ref_deed_no_date')->nullable();
            $table->string('misscase_no')->nullable();
            $table->date('misscase_date')->nullable();
            $table->date('misscase_judgement_date')->nullable();
            $table->longText('description');
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
        Schema::dropIfExists('s_a_mutation_infos');
    }
};
