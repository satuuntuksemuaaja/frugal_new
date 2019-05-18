<?php

use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Lead Sources");
        \Illuminate\Support\Facades\DB::statement('truncate table lead_sources');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.sources")->get() as $source)
        {
            (new \FK3\Models\LeadSource)->create([
                'id'     => $source->id,
                'name'   => $source->type,
                'active' => $source->active
            ]);
        }

    }
}
