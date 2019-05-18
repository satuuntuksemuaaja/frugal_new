<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Quote Question Answers migration
 *
 * This was formerly `quote_questions` in FK2
 *
 */
class AddQuoteQuestionAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_question_answers', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->integer('question_id');
            $t->integer('quote_id');
            $t->integer('group_id');
            $t->string('answer');
            $t->boolean('active');

            $t->index('question_id');
            $t->index('quote_id');
            $t->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_question_answers');
    }
}
