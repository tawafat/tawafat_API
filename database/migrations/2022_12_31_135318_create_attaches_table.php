<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attaches', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("type")->nullable();
            $table->string("url")->nullable();
            $table->string("size")->nullable();
            $table->string("folder")->default('general');
            $table->string("note")->nullable();
            $table->string("description")->nullable();
            $table->integer("created_by_id")->nullable();
            $table->integer("counter")->default(0);
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
        Schema::dropIfExists('attaches');
    }
}
