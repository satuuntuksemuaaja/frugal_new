<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
        });

        Schema::create('group_acls', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->integer('group_id');     // admins
            $t->integer('acl_id');       // admin.all
            $t->boolean('read')->default(0);    // can read, write, delete.
            $t->boolean('write')->default(0);
            $t->boolean('delete')->default(0);

            $t->index('group_id');
            $t->index('acl_id');
        });

        Schema::create('acls', function($t)
        {
            $t->increments('id');
            $t->timestamps();
            $t->string('acl');     // lead.board
            $t->string('page');    // Leads
            $t->string('action');     // Show all Leads
            $t->string('description'); // This is the ACL for showing all the leads.
        });

        Schema::table('users', function($t)
        {
           $t->integer('group_id')->default(0);
           $t->integer('customer_id')->default(0);

           $t->index('group_id');
           $t->index('customer_id');
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
            $t->dropColumn('group_id');
            $t->dropColumn('customer_id');
        });

        Schema::drop('group_acls');
        Schema::drop('groups');
        Schema::drop('acls');
    }
}
