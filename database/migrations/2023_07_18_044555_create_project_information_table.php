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
        Schema::create('project_information', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('district')->nullable();
            $table->string('thana')->nullable();
            $table->string('area')->nullable();
            $table->integer('number_of_mouza')->default(0);
            $table->string('map_image')->nullable();
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
        Schema::dropIfExists('project_information');
    }
};
