<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('end_users', function (Blueprint $table) {

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 3)->nullable();
            $table->string('mobile_no', 15)->nullable();

            $table->dropForeign('end_users_agent_id_foreign');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('end_users', function (Blueprint $table) {

        });
    }
}
