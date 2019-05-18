<?php

use Illuminate\Database\Seeder;

class CabinetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Cabinets");
        \Illuminate\Support\Facades\DB::statement('truncate table cabinets');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.cabinets")->get() as $cabinet)
        {
            (new \FK3\Models\Cabinet)->create([
                'id'                => $cabinet->id,
                'frugal_name'       => $cabinet->frugal_name,
                'name'              => $cabinet->name,
                'price'             => $cabinet->price,
                'vendor_id'         => $cabinet->vendor_id,
                'active'            => $cabinet->active,
                'description'       => $cabinet->description,
                'image'             => $cabinet->image,
            ]);
        }
    }
}
