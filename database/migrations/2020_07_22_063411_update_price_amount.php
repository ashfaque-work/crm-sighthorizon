<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePriceAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Price Field in Particular Tables

        // deals
        Schema::table(
            'deals', function (Blueprint $table){
            $table->float('price', 15, 2)->default('0.00')->change();
        }
        );

        // estimation_products
        Schema::table(
            'estimation_products', function (Blueprint $table){
            $table->float('price', 15, 2)->default('0.00')->change();
        }
        );

        // products
        Schema::table(
            'products', function (Blueprint $table){
            $table->float('price', 15, 2)->default('0.00')->change();
        }
        );

        // inovice_products
        Schema::table(
            'invoice_products', function (Blueprint $table){
            $table->float('price', 15, 2)->default('0.00')->change();
        }
        );

        // Update Amount Field in Particular Tables

        // expenses
        Schema::table(
            'expenses', function (Blueprint $table){
            $table->float('amount', 15, 2)->default('0.00')->change();
        }
        );

        // invoice_payments
        Schema::table(
            'invoice_payments', function (Blueprint $table){
            $table->float('amount', 15, 2)->default('0.00')->change();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // deals
        Schema::table(
            'deals', function (Blueprint $table){
            $table->float('price',15,2)->default('0.00');
        }
        );

        // estimation_products
        Schema::table(
            'estimation_products', function (Blueprint $table){
            $table->float('price',15,2)->default('0.00');
        }
        );

        // products
        Schema::table(
            'products', function (Blueprint $table){
            $table->float('price',15,2)->default('0.00');
        }
        );

        // invoice_products
        Schema::table(
            'invoice_products', function (Blueprint $table){
            $table->float('price', 15, 2)->default('0.00')->change();
        }
        );

        // expense
        Schema::table(
            'expense', function (Blueprint $table){
            $table->float('amount',15,2)->default('0.00');
        }
        );

        // invoice_payments
        Schema::table(
            'invoice_payments', function (Blueprint $table){
            $table->float('amount',15,2)->default('0.00');
        }
        );
    }
}
