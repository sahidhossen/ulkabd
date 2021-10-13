<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CsvImportController extends Controller
{

    use DispatchesJobs;

    public $import_id;
    public $import_log;
    public $collection;


    public function __construct( $import )
    {
        $this->import_id = $import->id;
        $this->import_log = $import;
        Log::info('csv import: '.$import);
    }

    public function import(){
         $path = Storage::url('app/'.$this->import_log->csv_path);

         $this->collection = $this->getCsv($path);

         Log::info('Collection: '.$this->collection);

        return true;
    }

    /**
     * @param $file
     * @return mixed
     */
    public function getCsv($file)
    {
        /*
         * implement error handling!!!!!!!
         */
        try {

            $csv = Excel::load($file);

            $csv->all();

            return $csv->parsed;

        }
        catch(\PHPExcel_Reader_Exception $e){

            return false;

        }
    }

}
