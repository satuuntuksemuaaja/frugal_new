<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add QuestionConditions
 *
 * This was formerly `conditions` in FK2 
 */
class AddQuoteQuestionConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_question_conditions', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->integer('question_id');
            $t->string('answer');
            $t->enum('operand', ['Add', 'Subtract']);
            $t->double('amount', 10, 2);
            $t->boolean('once');
            $t->boolean('active');
            $t->integer('percentage')->default(100);

            $t->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_question_conditions');
    }
}
