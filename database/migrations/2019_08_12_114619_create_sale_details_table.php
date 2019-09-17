<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity')->nullable();
            $table->integer('returned_quantity')->nullable();
            $table->double('unit_price')->nullable();
            $table->double('discount')->nullable();
            $table->double('tax')->nullable();
            $table->double('grand_total')->nullable();
            $table->double('commission')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_details');
    }
}
