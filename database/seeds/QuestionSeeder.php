<?php

use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Questions");
        \Illuminate\Support\Facades\DB::statement('truncate table quote_questions');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.questions")->get() as $question)
        {
            (new \FK3\Models\Question)->create([
                'id' => $question->id,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
                'question' => $question->question,
                'response_type' => $question->response_type,
                'stage' => $question->stage,
                'group_id' => $question->designation_id,
                'contract' => $question->contract,
                'contract_format' => $question->contract_format,
                'active' => $question->active,
                'question_category_id' => $question->question_category_id,
                'vendor_id' => $question->vendor_id,
                'small_job' => $question->small_job,
                'on_checklist' => $question->on_checklist,
                'on_job_board' => $question->on_job_board,
            ]);
        }
    }
}
