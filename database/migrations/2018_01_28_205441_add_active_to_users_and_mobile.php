<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveToUsersAndMobile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($t)
        {
           $t->boolean('active')->default(1);
           $t->string('mobile')->nullable();
           $t->string('hash')->nullable();
           $t->string('google_token', 2048)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($t)
        {
            $t->dropColumn('active');
            $t->dropColumn('mobile');
            $t->dropColumn('hash');
            $t->dropColumn('google_token', 2048);
        });
    }
}
