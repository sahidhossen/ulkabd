<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('agent_code')->unique();
            $table->string('agent_name')->nullable();
            $table->string('fb_page_id')->nullable();
            $table->string('fb_page_name')->nullable();
            $table->string('image_path')->nullable();
            $table->text('apiai_dev_access_token')->nullable();
            $table->text('apiai_client_access_token')->nullable();
            $table->boolean('is_default_intents_fetched')->default(false);
            $table->text('fb_access_token')->nullable();
            $table->string('fb_verify_token')->nullable();
            $table->integer('fb_likes_count')->unsigned()->default(0);
            $table->integer('fb_opt_in_count')->unsigned()->default(0);
            $table->boolean('is_fb_webhook')->default(false);
            $table->boolean('page_subscription')->default(false);
            $table->boolean('messenger_profile')->default(false);
            $table->boolean('is_apiai_fb_integration')->default(false);
            $table->integer('training_status')->default(2);
            $table->boolean('is_payment_due')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('agents');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
