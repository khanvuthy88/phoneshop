<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('creator_id')->comment('User who inserted data');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('staff_id');
            $table->date('sale_date')->nullable();
            $table->string('sale_code')->nullable();
            $table->string('reference_no')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->double('total_price')->nullable();
            $table->string('discount_type')->nullable();
            $table->double('total_discount')->nullable();
            $table->double('shipping_amount')->nullable();
            $table->text('shipping_note')->nullable();
            $table->double('total_tax')->nullable();
            $table->double('grand_total')->nullable();
            $table->double('paid_amount')->nullable();
            $table->double('total_commission')->nullable();

            $table->string('payment_installment')->nullable();
            $table->date('payment_start_date')->nullable();
            $table->date('payment_end_date')->nullable();
            $table->integer('payment_period')->nullable();

            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->text('staff_note')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('staff_Id')->references('id')->on('staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
