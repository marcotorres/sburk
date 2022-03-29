<?php

use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plans')->insert([
            [
                'name' => 'Free',
                'price' => 0,
                'allowed_drivers' => 3,
            ],
        ]);
    }
}
