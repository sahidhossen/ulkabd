<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrebuiltAgent extends Model
{
    protected $table = "prebuilt_agents";

    protected $fillable = [
        'name',
        'agent_id',
        'apiai_dev_access_token',
        'apiai_client_access_token',
        'is_taken'
    ];
}
