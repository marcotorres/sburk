<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllSettingsToParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parents', function (Blueprint $table) {
            //
            $table->boolean('arrived_school')->default(true);
            $table->boolean('left_school')->default(true);
            $table->boolean('arrived_home')->default(true);
            $table->boolean('left_home')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parents', function (Blueprint $table) {
            //
            $table->dropColumn('arrived_school');
            $table->dropColumn('left_school');
            $table->dropColumn('arrived_home');
            $table->dropColumn('left_home');
        });
    }
}
