<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained();
           // $table->enum('job_type', ['visiting_camps', 'visiting_gates', 'food_area']);
            $table->string('job_type')->nullable();
            $table->integer('no_of_packages')->nullable();
            $table->integer('rejected_packages')->nullable();
            $table->integer('min_weight')->nullable();
            $table->integer('gate_number')->nullable();
            $table->integer('no_entering')->nullable();
            $table->integer('no_exiting')->nullable();
            $table->integer('no_inside')->nullable();
            $table->integer('camp_number')->nullable();
            $table->integer('temperature')->nullable();
            $table->integer('humidity')->nullable();
            $table->dateTime('date_time')->nullable();
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
        Schema::dropIfExists('job_details');
    }
}
