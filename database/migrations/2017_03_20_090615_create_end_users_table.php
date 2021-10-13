<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEndUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('end_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id')->unsigned();
            $table->string('agent_scoped_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('platform')->nullable()->comment('facebook, twitter, google');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('local')->nullable();
            $table->text('profile_pic')->nullable();

            $table->timestamps();
            $table->foreign('agent_id')->references('id')->on('agents');

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
        Schema::dropIfExists('end_users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    }
}
