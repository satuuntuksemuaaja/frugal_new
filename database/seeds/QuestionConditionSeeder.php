<?php

use Illuminate\Database\Seeder;

class QuestionConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("QuestionConditions");
        \Illuminate\Support\Facades\DB::statement('truncate table quote_question_conditions');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.conditions")->get() as $condition)
        {
            (new \FK3\Models\QuestionCondition)->create([
                'id' => $condition->id,
                'created_at' => $condition->created_at,
                'updated_at' => $condition->updated_at,
                'question_id' => $condition->question_id,
                'answer' => $condition->answer,
                'operand' => $condition->operand,
                'amount' => $condition->amount,
                'once' => $condition->once,
                'active' => $condition->deleted_at === null ? true : false,
            ]);
        }
    }
}
