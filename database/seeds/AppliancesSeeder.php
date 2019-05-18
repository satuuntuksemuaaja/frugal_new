<?php

use FK3\Models\Appliance;
use Illuminate\Database\Seeder;

class AppliancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Appliance Seeds");
        \Illuminate\Support\Facades\DB::statement('truncate table appliances');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.appliances")->get() as $app)
        {
            (new Appliance)->create([
                'id'       => $app->id,
                'name'     => $app->name,
                'price'    => $app->price,
                'count_as' => $app->countas,
                'group_id' => $app->designation_id,
                'active'   => $app->active
            ]);
        }
    }
}
