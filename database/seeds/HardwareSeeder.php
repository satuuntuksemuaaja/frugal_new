<?php

use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Hardware");
        \Illuminate\Support\Facades\DB::statement('truncate table hardwares');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.hardwares")->get() as $hardware)
        {
            (new \FK3\Models\Hardware)->create([
                'id'                => $hardware->id,
                'sku'               => $hardware->sku,
                'description'       => $hardware->description,
                'vendor_id'         => $hardware->vendor_id,
                'price'             => $hardware->price,
                'active'            => $hardware->active,
                'image'             => $hardware->image,
            ]);
        }
    }
}
