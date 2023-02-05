<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('type');
            $table->json('data');
            $table->integer('created_by_id');

            $table->string('predicate_table_name')->nullable();
            $table->string('predicate_id')->nullable();

            $table->unsignedInteger('loggable_id');
            $table->string('loggable_type');

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
        Schema::dropIfExists('data_logs');
    }
}
