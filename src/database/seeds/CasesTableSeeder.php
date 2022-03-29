<?php

use Illuminate\Database\Seeder;

class CasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cases
        DB::table('cases')->insert([
            [
                'name' => 'arrive',
            ],
            [
                'name' => 'leave',
            ],
            [
                'name' => 'checkIn',
            ],
            [
                'name' => 'checkOut',
            ],
        ]);
    }
}
