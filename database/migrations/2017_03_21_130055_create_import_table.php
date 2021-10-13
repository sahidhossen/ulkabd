<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('csv_name');
            $table->string('csv_path');
            $table->string('agent_id')->nullable();
            $table->integer('csv_rows_count')->unsigned()->nullable();
            $table->integer('success_rows')->unsigned()->nullable()->default(0);
            $table->boolean('is_active')->default(false);
            $table->string('state')->nullable();
            $table->string('soft_errors')->nullable();
            $table->string('hard_errors')->nullable();
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
        Schema::dropIfExists('imports');
    }
}
