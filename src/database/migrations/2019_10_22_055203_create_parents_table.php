<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->double('address_latitude')->nullable()->default(null);
            $table->double('address_longitude')->nullable()->default(null);
            
            $table->string('tel_number');
            $table->string('country_code');

            $table->string('fcm_token')->nullable()->default(null);

            $table->double('zone_alert_distance')->nullable()->default(null);

            $table->unsignedInteger('driver_id')->nullable();
            $table->unsignedInteger('school_id');
            
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('school_id')->references('id')->on('schools');

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
        Schema::dropIfExists('parents');
    }
}
