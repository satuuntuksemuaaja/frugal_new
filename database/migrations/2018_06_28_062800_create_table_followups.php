<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFollowups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('followups')) { return; }
        Schema::create('followups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stage');
            $table->integer('lead_id');
            $table->integer('status_id');
            $table->integer('user_id');
            $table->text('comments');
            $table->integer('closed')->default(0);

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
        Schema::dropIfExists('followups');
    }
}
