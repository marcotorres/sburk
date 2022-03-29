<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverSchoolVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_school_visits', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('driver_id');
            
            $table->unsignedInteger('case_id');

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');

            $table->foreign('case_id')->references('id')->on('cases');

            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_school_visits');
    }
}
