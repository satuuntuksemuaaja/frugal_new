<?php

use Illuminate\Database\Seeder;

class CountertopTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Countertop Types");

        \Illuminate\Support\Facades\DB::statement('truncate table countertop_types');
        $materials = [
            'granite',
            'quartz',
            'marble',
            'concrete',
            'wood',
        ];
        foreach ($materials as $material) {
            (new \FK3\Models\CountertopType)->create([
                'name'              => $material,
            ]);
        }
    }
}
