<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddACLGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acl_categories', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
        });

        Schema::table('acls', function($t)
        {
           $t->dropColumn('page');
           $t->integer('acl_category_id');

           $t->index('acl_category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('acl_categories');

        Schema::table('acls', function($t)
        {
            $t->string('page');
            $t->dropColumn('acl_category_id');
        });
    }


}
