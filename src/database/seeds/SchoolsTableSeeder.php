<?php

use Illuminate\Database\Seeder;

class SchoolsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create super admin account
        $user = \App\User::create([
            'name' => 'SuperAdmin',
            'email' => 'superadmin@sburk.com',
            'password' => bcrypt('admin'),
            'is_super_admin_account' => 1,
            'avatar' => 'avatar.png'
        ]);

        $avatar = Avatar::create($user->name)->getImageObject()->encode('png');
        Storage::put('avatars/'.$user->id.'/avatar.png', (string) $avatar);
    }
}
