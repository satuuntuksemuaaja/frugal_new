<?php

use FK3\Models\Acl;
use FK3\Models\AclCategory;
use Illuminate\Database\Seeder;

class BaseACLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("Making this thing actually work..");
        \Illuminate\Support\Facades\DB::statement('truncate table acl_categories');
        \Illuminate\Support\Facades\DB::statement('truncate table acls');


        AclCategory::create([
            'name' => 'Administrative Functions'
        ]);
        Acl::create([
            'acl'             => 'admin.main',
            'acl_category_id' => 1,
            'action'          => 'Access Main Area',
            'description'     => 'This ACL will enable the user to see the admin menu option to the left.'
        ]);
        Acl::create([
            'acl'             => 'admin.users',
            'acl_category_id' => 1,
            'action'          => 'Manage Users',
            'description'     => 'This this allows access to manage the users.     '
        ]);

    }
}
