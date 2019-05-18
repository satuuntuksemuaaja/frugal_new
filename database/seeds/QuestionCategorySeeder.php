<?php

use Illuminate\Database\Seeder;

class QuestionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("QuestionCategories");
        \Illuminate\Support\Facades\DB::statement('truncate table question_categories');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.question_categories")->get() as $questionCategory)
        {
            (new \FK3\Models\QuestionCategory)->create([
                'id' => $questionCategory->id,
                'name' => $questionCategory->name,
            ]);
        }
    }
}
