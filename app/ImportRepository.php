<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportRepository extends Model
{
    use DispatchesJobs;

    public $import_id;
    public $import_log;
    public $collection;


    public function __construct(  )
    {

    }



}
