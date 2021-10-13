<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable, EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name' ,'email', 'howmany_agents', 'password','user_name','secondary_email','tertiary_email','email_token','mobile_no','phone_no'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    /*
     * Get user roles based their permission assigned
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    /*
     * get agent information own by user
     */
    public function agents(){
        return $this->hasMany('App\Agents');
    }

    /*
    * get agent information own by user
    */
    public function business_identity(){
        return $this->hasOne('App\BusinessIdentity');
    }

}
