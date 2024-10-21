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
        Schema::create('plot_information', function (Blueprint $table) {
            $table->id();
            $table->date('project_start_date');
            $table->string('project_name');
            $table->integer('no_of_plot')->default(0);
            $table->float('road_width');
            $table->string('file_no');
            $table->string('road_number_name');
            $table->string('plot_facing');
            $table->string('sector')->nullable();
            $table->float('plot_size_katha');
            $table->float('plot_size_ojutangsho');
            $table->string('plot_dimension');
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
        Schema::dropIfExists('plot_information');
    }
};
