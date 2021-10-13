<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->increments('id');

            $table->integer('agent_id')->unsigned();
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('end_user_id')->unsigned();

            $table->string('buyer_name');
            $table->string('mobile_no', 18)->nullable();
            $table->text('address')->nullable();

            $table->string('product_code');
            $table->string('product_name');
            $table->text('attribute_values')->nullable();
            $table->integer('price');
            $table->integer('quantity');

            $table->integer('delivery_charge')->default(0);
            $table->smallInteger('status')->default(0);// new = 0, delivered = 1, sent = 2, cancelled = 3
            $table->string('order_code')->unique();
            $table->text('status_detail')->nullable();
            $table->tinyInteger('payment_status')->default(0);//due = 0, paid = 1, pending = 2

            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('end_user_id')->references('id')->on('end_users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('orders');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
