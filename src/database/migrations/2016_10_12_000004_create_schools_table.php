<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('channel')->nullable();
            $table->string('address')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->boolean('is_super_admin_account')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->default('avatar.png');
            
            $table->date('plan_renews_at')->nullable()->default(null);
            $table->unsignedInteger('plan_id')->default(1);
            
            $table->foreign('plan_id')->references('id')->on('plans');

            $table->string('stripe_id')->nullable()->index();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->rememberToken();
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
        Schema::dropIfExists('schools');
    }
}
