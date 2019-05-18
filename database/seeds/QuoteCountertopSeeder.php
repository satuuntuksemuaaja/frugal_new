<?php

use Illuminate\Database\Seeder;

class QuoteCountertopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Quote Countertops");
        \Illuminate\Support\Facades\DB::statement('truncate table quote_countertops');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.quote_granites")->get() as $granite)
        {
            (new \FK3\Models\QuoteCountertop)->create([
                'id' => $granite->id,
                'created_at' => $granite->created_at,
                'updated_at' => $granite->updated_at,
                'quote_id' => $granite->quote_id,
                'description' => $granite->description,
                'countertop_id' => $granite->granite_id,
                'granite_override' => $granite->granite_override,
                'pp_sqft' => $granite->pp_sqft,
                'removal_type' => $granite->removal_type,
                'measurements' => $granite->measurements,
                'counter_edge' => $granite->counter_edge,
                'counter_edge_ft' => $granite->counter_edge_ft,
                'backsplash_height' => $granite->backsplash_height,
                'raised_bar_length' => $granite->raised_bar_length,
                'raised_bar_depth' => $granite->raised_bar_depth,
                'island_width' => $granite->island_width,
                'island_length' => $granite->island_length,
                'countertop_jo' => $granite->granite_jo,
            ]);
        }
    }
}
