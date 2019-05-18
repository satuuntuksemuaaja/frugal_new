<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->command->confirm('This will truncate all tables and re-seed. It can take up to 30 minutes. Are you sure?')) {
            $this->call(UserSeeder::class);
            $this->call(LeadSourceSeeder::class);
            $this->call(VendorSeeder::class);
            $this->call(SinkSeeder::class);
            $this->call(GroupSeeder::class);
            $this->call(AppliancesSeeder::class);
            $this->call(BaseACLSeeder::class);
            $this->call(CabinetSeeder::class);
            $this->call(HardwareSeeder::class);
            $this->call(AddonSeeder::class);
            $this->call(CountertopTypeSeeder::class);
            $this->call(CountertopSeeder::class);
            $this->call(CustomerSeeder::class);
            $this->call(QuoteCountertopSeeder::class);
            $this->call(QuestionCategorySeeder::class);
            $this->call(QuestionSeeder::class);
            $this->call(QuestionConditionSeeder::class);
            $this->call(ResponsibilitySeeder::class);
            $this->call(QuoteResponsibilitySeeder::class);
            $this->call(QuestionAnswerSeeder::class);
            $this->call(PunchSeeder::class);
            $this->call(LeadSeeder::class);
        }
    }
}
