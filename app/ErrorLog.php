<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use League\Flysystem\Exception;

class ErrorLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'error_type', 'table_name' ,'details'
    ];


    /*
     * Hard Error
     */
    public static function write( $type="", $details, $table="" ){
        $error_log = new ErrorLog();
        try{
            $error_log->error_type = $type;
            $error_log->details = $details;
            $error_log->table_name = $table;
            $error_log->save();
            return $error_log->id;
        }catch( Exception $e ){
            App::abort(403, 'Erro write invalied.');
        }

    }
}
