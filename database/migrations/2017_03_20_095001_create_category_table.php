<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('agent_id')->unsigned();
            $table->string('name');
            $table->string('description')->nullable();
            $table->text('required_attributes')->nullable()->comment('color,size')->default(null);
            $table->string('image')->nullable();
            $table->string('next')->nullable();
            $table->unsignedInteger('prev')->nullable();
            $table->string('apiai_intent_id')->nullable();
            $table->string('apiai_intent_name')->nullable();
            $table->string('apiai_entity_id')->nullable();
            $table->string('apiai_entity_name')->nullable();
            $table->integer('flag')->default(0);

            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('prev')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('categories');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
