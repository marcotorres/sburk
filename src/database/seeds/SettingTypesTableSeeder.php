<?php

use Illuminate\Database\Seeder;

class SettingTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('setting_types')->insert([
            [
                'id' => 1,
                'name' => 'Google Maps',
                'enabled' => null,
            ],
        ]);
        DB::table('setting_types')->insert([
            [
                'id' => 2,
                'name' => 'Personalize your system',
                'enabled' => null,
            ],
        ]);
        DB::table('setting_types')->insert([
            [
                'id' => 3,
                'name' => 'Currency and billing cycle',
                'enabled' => null,
            ],
        ]);
        DB::table('setting_types')->insert([
            [
                'id' => 4,
                'name' => 'SMS',
                'enabled' => null,
            ],
        ]);
        DB::table('setting_types')->insert([
            [
                'id' => 5,
                'name' => 'Stripe',
            ],
        ]);
    }
}
