<?php

use Illuminate\Database\Seeder;

class CountertopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Countertops");
        
        $types = \FK3\Models\CountertopType::pluck('id', 'name')->all();
        \Illuminate\Support\Facades\DB::statement('truncate table countertops');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.granites")->get() as $granite)
        {
            $type = $types['granite'];

            if (stristr($granite->name, 'quartz')) {
                $type = $types['quartz'];
            }
            if (stristr($granite->name, 'marble')) {
                $type = $types['marble'];
            }

            (new \FK3\Models\Countertop)->create([
                'id'                => $granite->id,
                'name'              => $granite->name,
                'price'             => $granite->price,
                'active'            => $granite->active,
                'type_id'           => $type,
            ]);
        }
    }
}
