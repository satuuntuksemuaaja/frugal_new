<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('contacts')) { return; }
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('name', 255);
            $table->string('email');
            $table->string('mobile', 20)->nullable();
            $table->string('home', 20)->nullable();
            $table->string('alternate', 20)->nullable();
            $table->tinyInteger('primary')->nullable();
            $table->softDeletes();
            $table->timestamps();

            if (Schema::hasTable('contacts')) {
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
