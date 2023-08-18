<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_partners', function (Blueprint $table) {
            $table->id();
            $table->string('code_product')->nullable();
            $table->string('name')->nullable();
            $table->string('price_partner')->nullable();
            $table->string('link_product')->nullable();
            $table->string('category_id')->nullable();
            $table->string('status')->default(1)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_partners');
    }
}
