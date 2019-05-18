<?php

use Illuminate\Database\Seeder;

class ResponsibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Responsibilities");
        \Illuminate\Support\Facades\DB::statement('truncate table responsibilities');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.responsibilities")->get() as $responsibility)
        {
            (new \FK3\Models\Responsibility)->create([
                'id' => $responsibility->id,
                'created_at' => $responsibility->created_at,
                'updated_at' => $responsibility->updated_at,
                'name' => $responsibility->name,
                'active' => $responsibility->active,
            ]);
        }
    }
}
