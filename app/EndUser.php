<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use League\Flysystem\Exception;

class EndUser extends Model
{
    protected $table = 'end_users';

    protected $fillable = [
        'agent_id',
        'agent_scoped_id',
        'session_id',
        'platform',
        'first_name',
        'last_name',
        'gender',
        'local',
        'profile_pic',
        'created_at',
        'updated_at',
        'date_of_birth',
        'emails'
    ];

    /*
     * Get a agents ID
     */
    public function agent()
    {
        return $this->belongsTo('App\Agents');
    }

    public static function updateProfile($data)
    {
        try {
            $end_user = EndUser::find($data['system_user_id']);
            if (!isset($end_user)) {
                throw new Exception("Invalid user!");
            }

            $names = explode(' ', $data['name']);

            if (is($names) && count($names) > 0) {
                $end_user->first_name = $names[0];

                if (count($names) > 1) {
                    $end_user->last_name = implode(' ', array_slice($names, 1));
                }
            }

            $addresses = [];
            if (isset($data['mailing_address']['street_1'])) array_push($addresses, $data['mailing_address']['street_1']);
            if (isset($data['mailing_address']['street_2'])) array_push($addresses, $data['mailing_address']['street_2']);

            $end_user->date_of_birth = isset($data['date_of_birth']) ? $data['date_of_birth'] : null;
            $end_user->gender = $data['gender'];
            $end_user->address = json_encode($addresses);
            $end_user->zip = $data['mailing_address']['zip'];
            $end_user->city = $data['mailing_address']['city'];
            $end_user->country = $data['mailing_address']['country'];
            $end_user->emails = isset($data['email']) ? json_encode($data['email']) : null;
            $end_user->mobile_no = $data['phone'][0];
            $end_user->save();

        } catch(Exception $e) {
            throw $e;
        }
    }

    public static function getProfile($id) {
        try {
            $end_user = EndUser::find($id);
            if (!isset($end_user)) {
                throw new Exception("Invalid user!");
            }

            $end_user->address = json_decode($end_user->address, true);
            $end_user->emails = json_decode($end_user->emails, true);

            return $end_user;

        } catch (Exception $e) {
            throw $e;
        }
    }

}
