<?php

use Illuminate\Database\Seeder;

class QuestionAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // in fk2 the table is called quote_questions.  These are essentially just
        // answers to the questions (in the context of building a quote), so it makes
        // sense to me to call the table quote_question_answers.
        $this->command->alert("QuestionAnswers");
        \Illuminate\Support\Facades\DB::statement('truncate table quote_question_answers');
        $questions = \Illuminate\Support\Facades\DB::table("vocalcrm.questions")->pluck('id');

        // There are currently many 'orphaned' answers whose question_id is no longer present;
        $notOrphaned = \Illuminate\Support\Facades\DB::table("vocalcrm.quote_questions")
            ->whereIn('question_id', $questions);

        foreach ($notOrphaned->get() as $quoteQuestion)
        {
            (new \FK3\Models\QuestionAnswer)->create([
                'id' => $quoteQuestion->id,
                'created_at' => $quoteQuestion->created_at,
                'updated_at' => $quoteQuestion->updated_at,
                'question_id' => $quoteQuestion->question_id,
                'quote_id' => $quoteQuestion->quote_id,
                'answer' => $quoteQuestion->answer,
                'active' => $quoteQuestion->deleted_at === null ? '1' : 0,
            ]);
        }
    }
}
