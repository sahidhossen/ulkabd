<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrebuiltAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prebuilt_agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('agent_id')->unsigned()->nullable();
            $table->text('apiai_dev_access_token');
            $table->text('apiai_client_access_token');
            $table->boolean('is_taken')->default(false);
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('prebuilt_agents');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
