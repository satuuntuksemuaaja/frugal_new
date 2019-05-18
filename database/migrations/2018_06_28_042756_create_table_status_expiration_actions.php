<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStatusExpirationActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('status_expiration_actions')) { return; }
        Schema::create('status_expiration_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status_expiration_id');
            $table->string('description', 255);
            $table->integer('sms')->default(0);
            $table->string('email_subject', 255);
            $table->integer('email')->default(0);
            $table->text('email_content')->nullable();
            $table->text('sms_content')->nullable();
            $table->integer('group_id');
            $table->text('meta')->nullable();
            $table->integer('active')->default(1);
            $table->text('attachment')->nullable();

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
        Schema::dropIfExists('status_expiration_actions');
    }
}
