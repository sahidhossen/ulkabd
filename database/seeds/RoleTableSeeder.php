<?php

use Illuminate\Database\Seeder;
use App\Role;
class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $owner = new Role();
        $owner->name         = 'ulkabot';
        $owner->display_name = 'Ulkabot Owner'; // optional
        $owner->description  = 'Owner of the ulkabot'; // optional
        $owner->save();

        $admin = new Role();
        $admin->name         = 'admin';
        $admin->display_name = 'Administrator'; // optional
        $admin->description  = 'User is allowed to manage and edit other employers'; // optional
        $admin->save();

        $user = new Role();
        $user->name         = 'user';
        $user->display_name = 'Employer'; // optional
        $user->description  = 'User are allowed to manage which permission by client'; // optional
        $user->save();

    }
}
