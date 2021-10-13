<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessIdentity extends Model
{


    /*
     * Table Name
     */
    protected $table = "business_identity";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'business_name' ,'address', 'zip_code','country','state','city','govt_business_id','created_at','updated_at'
    ];


    /**
     * Get the user that owns the business.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
