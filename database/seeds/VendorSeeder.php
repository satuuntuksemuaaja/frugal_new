<?php

use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Vendors");
        \Illuminate\Support\Facades\DB::statement('truncate table vendors');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.vendors")->get() as $vendor)
        {
            (new \FK3\Models\Vendor)->create([
                'id'                => $vendor->id,
                'name'              => $vendor->name,
                'shipping_days'     => $vendor->tts,
                'multiplier'        => $vendor->multiplier,
                'freight'           => $vendor->freight,
                'build_up'          => $vendor->buildup,
                'colors'            => $vendor->colors,
                'active'            => $vendor->active,
                'wood_products'     => $vendor->wood_products,
                'confirmation_days' => $vendor->confirmation_days
            ]);
        }
    }
}
