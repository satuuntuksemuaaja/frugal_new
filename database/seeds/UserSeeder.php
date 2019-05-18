<?php

use FK3\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->alert("Starting FK2 User Import");
        \Illuminate\Support\Facades\DB::statement('truncate table users');
        foreach (\Illuminate\Support\Facades\DB::table("vocalcrm.users")->get() as $user)
        {
            try
            {
                (new User)->create([
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'password'     => $user->password,
                    'active'       => $user->active,
                    'mobile'       => $user->mobile,
                    'hash'         => $user->bypass,
                    'google_token' => $user->google,
                    'group_id'     => $user->designation_id
                ]);
                // $this->command->info("Created $user->email");
            } catch (Exception $e)
            {
                $this->command->error("Could not create $user->email.. ");
            }
        }
        $this->command->info("Setting USER 1 to Group 13 (Administrators)");
        User::find(1)->update(['group_id' => 13]);
    }
}
