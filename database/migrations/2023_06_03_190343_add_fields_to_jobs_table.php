<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
            Schema::table('jobs', function (Blueprint $table) {
                $table->boolean('enable_gps')->default(true)->after('category_slug');
                $table->boolean('enable_studio')->default(true)->after('enable_gps');
                $table->string('type')->nullable()->after('enable_studio');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropColumn('enable_gps');
                $table->dropColumn('enable_studio');
                $table->dropColumn('type');
            });
        });
    }
}
