<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id','csv_name', 'csv_path' ,'state','csv_rows_count','success_rows','is_active'
    ];
}
