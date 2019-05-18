<?php

use Illuminate\Database\Seeder;

class QuoteResponsibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Quote Responsibilties");
        \Illuminate\Support\Facades\DB::statement('truncate table quote_responsibilities');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.quote_responsibilities")->get() as $quoteResponsibility)
        {
            (new \FK3\Models\QuoteResponsibility)->create([
                'id' => $quoteResponsibility->id,
                'created_at' => $quoteResponsibility->created_at,
                'updated_at' => $quoteResponsibility->updated_at,
                'quote_id' => $quoteResponsibility->quote_id,
                'responsibility_id' => $quoteResponsibility->responsibility_id,
            ]);
        }
    }
}
