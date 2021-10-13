<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcastTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id')->unsigned();
            $table->integer('subscription_topics_id')->unsigned()->nullable();
            $table->text('creative');
            $table->string('ext_creative_id')->nullable();
            $table->string('ext_broadcast_id')->nullable();
            $table->text('schedule')->nullable();
            $table->text('stat')->nullable()->comments('statistical data');
            $table->integer('state')->default(0);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('subscription_topics_id')->references('id')->on('subscription_topics');
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
        Schema::dropIfExists('broadcasts');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
