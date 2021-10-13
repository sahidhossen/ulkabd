<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProuctTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->string('name');
            $table->string('code');
            $table->text('product_attributes')->nullable()->comment('color:red,green,blue;size:XL,L,M,S');
            $table->decimal('price',12,2)->nullable();
            $table->decimal('offer_price',12,2)->nullable();
            $table->integer('priority')->unsigned()->nullable();
            $table->text('detail')->nullable();
            $table->text('is_image')->nullable();
            $table->string('unit')->nullable()->comment('kg, litter, item');
            $table->integer('flag')->default(0);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories');
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
        Schema::dropIfExists('products');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
