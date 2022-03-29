<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('channel');

            $table->double('last_latitude')->nullable()->default(null);
            $table->double('last_longitude')->nullable()->default(null);

            $table->unsignedInteger('bus_id');
            $table->string('tel_number');
            $table->string('country_code');
            
            $table->unsignedInteger('school_id');
            
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('bus_id')->references('id')->on('buses');

            $table->string('secretKey');
            $table->string('v_code')->nullable()->default(null);
            $table->boolean('verified')->default(false);
            
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
        Schema::dropIfExists('drivers');
    }
}
