<?php

use FK3\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Group Seeds");
        \Illuminate\Support\Facades\DB::statement('truncate table groups');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.designations")->get() as $d)
        {
            (new Group)->create([
                'id'   => $d->id,
                'name' => $d->name
            ]);
        }
    }
}
