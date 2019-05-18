<?php

use Illuminate\Database\Seeder;

class SinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Sinks");
        \Illuminate\Support\Facades\DB::statement('truncate table sinks');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.sinks")->get() as $sink)
        {
            (new \FK3\Models\Sink)->create([
                'id'       => $sink->id,
                'name'     => $sink->name,
                'price'    => $sink->price,
                'material' => $sink->material,
                'active'   => $sink->active
            ]);
        }
    }
}
