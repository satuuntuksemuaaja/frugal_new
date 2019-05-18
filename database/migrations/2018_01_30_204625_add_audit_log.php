<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('user_id');
           $t->string('page');              // Lead Source Admin
           $t->string('action');            // Updated Lead Source
           $t->integer('quote_id')->nullable();
           $t->integer('customer_id')->nullable();
           $t->integer('lead_id')->nullable();

           $t->index('user_id');
           $t->index('quote_id');
           $t->index('customer_id');
           $t->index('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('audits');
    }
}
