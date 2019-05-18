<?php

use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Addons");
        \Illuminate\Support\Facades\DB::statement('truncate table addons');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.addons")->get() as $addon)
        {
            (new \FK3\Models\Addon)->create([
                'id'                => $addon->id,
                'item'              => $addon->item,
                'price'             => $addon->price,
                'active'            => $addon->active,
                'automatic'         => false,
                'contract'          => $addon->contract,
                'group_id'          => $addon->designation_id,
            ]);
        }

        // We are merging the addons and extras table into addons.
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.extras")->get() as $extra)
        {
            (new \FK3\Models\Addon)->create([
                'item'              => $extra->name,
                'price'             => $extra->price,
                'active'            => $extra->active,
                'automatic'         => true,
                'contract'          => '',
                'group_id'          => $extra->designation_id,
            ]);
        }
    }
}
