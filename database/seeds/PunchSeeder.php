<?php

use Illuminate\Database\Seeder;

class PunchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Punches");
        \Illuminate\Support\Facades\DB::statement('truncate table punches');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.punches")->get() as $punch)
        {
            (new \FK3\Models\Punch)->create([
                'id' => $punch->id,
                'created_at' => $punch->created_at,
                'updated_at' => $punch->updated_at,
                'group_id' => $punch->designation_id,
                'question' => $punch->question,
                'active' => $punch->active,
            ]);
        }
    }
}
