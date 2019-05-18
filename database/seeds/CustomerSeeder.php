<?php

use Illuminate\Database\Seeder;
use FK3\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Starting FK2 Customer Import");
        \Illuminate\Support\Facades\DB::statement('truncate table customers');


        $customerTable = new Customer;
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.customers")->get() as $customer)
        {
            $contact = \Illuminate\Support\Facades\DB::table("vocalcrm.contacts")->where('customer_id', $customer->id)
                ->first();
            if (!$contact) continue; // everyone had a contact.. but just in case for ones that may be like 5 years old.
            $data = [
                'id'          => $customer->id,
                'name'        => $customer->name,
                'address'     => $customer->address,
                'city'        => $customer->city,
                'state'       => $customer->state,
                'zip'         => $customer->zip,
                'active'    => $customer->archived ? 0 : 1,
                'job_address' => $customer->job_address,
                'job_city'    => $customer->job_city,
                'job_state'   => $customer->job_state,
                'job_zip'     => $customer->job_zip,
                'email'       => $contact->email,
                'mobile'      => $contact->mobile,
                'home'        => $contact->home,
                'alternate'   => $contact->alternate,
            ];
            $customerTable->create($data);

        }

    }
}
