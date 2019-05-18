<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteResponsibilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_responsibilities', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->integer('responsibility_id');

            $t->index('quote_id');
            $t->index('responsibility_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_responsibilities');
    }
}
