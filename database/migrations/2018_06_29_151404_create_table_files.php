<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('files')) { return; }
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->text('location', 255);
            $table->string('description', 255)->nullable();
            $table->integer('user_id');
            $table->integer('quote_id');
            $table->integer('attached')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('files');
    }
}
