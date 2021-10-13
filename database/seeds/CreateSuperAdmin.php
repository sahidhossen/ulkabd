<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use App\Agents;
use App\BusinessIdentity;

class CreateSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default user for Sahid Hossen
        $user = new User();
        $user->first_name = 'Sahid';
        $user->last_name = 'Hossen';
        $user->email = "sahid@mail.com";
        $user->password = bcrypt("xproject");
        $user->user_name = "sahid";
        $user->verified = 1;
        $user->save();

        $rootRole = Role::where('name', '=', 'ulkabot')->first();
        $user->attachRole($rootRole);

        $userBusiness = new BusinessIdentity();
        $userBusiness->business_name = "Twaha's Usha";
        $userBusiness->address = 'Bangladesh';
        $userBusiness->user_id = $user->id;
        $userBusiness->save();

        // Default admin user 'Jagadish Chandra Bose'
        $admin = new User();
        $admin->first_name = 'Jagadish Chandra';
        $admin->last_name = 'Bose';
        $admin->email = "bose@ulkabd.com";
        $admin->password = bcrypt("admin");
        $admin->user_name = "bose";
        $admin->verified = 1;
        $admin->save();

        $adminRole = Role::where('name', '=', 'admin')->first();
        $admin->attachRole($adminRole);

        $business = new BusinessIdentity();
        $business->business_name = "Bose's Usha";
        $business->address = 'Bangladesh';
        $business->user_id = $admin->id;
        $business->save();

        // Default agent for 'Jagadish Chandra Bose'
        $agent = new Agents();
        $agent->user_id = $admin->id;
        $agent->agent_name = 'Ulka Bot';
        $agent->agent_code = str_random(20);
        $agent->fb_verify_token = strtolower(str_random(5) . '_' . str_replace(' ', '', 'Ulka Bot'));
        $agent->apiai_client_access_token = '4d924731accb451faf21dc295178ca49';
        $agent->apiai_dev_access_token = 'bde1c0b1044a4cfe8ed21ebd50d244cd';
        $agent->is_default_intents_fetched = false;
        $agent->save();
    }
}
