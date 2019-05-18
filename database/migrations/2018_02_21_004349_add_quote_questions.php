<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_questions', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('question');
            $t->string('response_type');
            $t->string('stage');
            $t->integer('group_id');
            $t->boolean('contract')->default(0);
            $t->string('contract_format')->nullable();
            $t->boolean('active')->default(1);
            $t->integer('question_category_id');
            $t->integer('vendor_id');
            $t->boolean('small_job')->default(0);
            $t->boolean('on_checklist')->default(0);
            $t->boolean('on_job_board')->default(0);

            $t->index('group_id');
            $t->index('question_category_id');
            $t->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_questions');
    }
}
