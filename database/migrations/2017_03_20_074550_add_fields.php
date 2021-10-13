<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->renameColumn('name', 'first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_name')->unique()->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('tertiary_email')->nullable();
            $table->string('mobile_no',13)->nullable();
            $table->string('phone_no',18)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
