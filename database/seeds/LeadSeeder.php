<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/22/18
 * Time: 4:28 PM
 */

class LeadSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $this->command->alert("Leads");
        \Illuminate\Support\Facades\DB::statement('truncate table leads');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.leads")->get() as $lead)
        {
            (new \FK3\Models\Lead)->create([
                'created_at'  => $lead->created_at,
                'updated_at'  => $lead->updated_at,
                'id'          => $lead->id,
                'status_id'   => $lead->status_id,
                'customer_id' => $lead->customer_id,
                'source_id'   => $lead->source_id,
                'user_id'     => $lead->user_id,
                'title'       => $lead->title,
                'closed'      => $lead->closed,
                'archived'    => $lead->archived,
                'provided'    => $lead->provided
            ]);
        }
    }

}