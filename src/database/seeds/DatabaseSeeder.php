<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PlansTableSeeder::class);
        $this->call(SchoolsTableSeeder::class);
        $this->call(CasesTableSeeder::class);
        $this->call(SettingTypesTableSeeder::class); 
        $this->call(SettingsTableSeeder::class);    
    }
}
