<?php

namespace App\Jobs;

use App\Agents;
use App\Events\BroadcastTrainingStatus;
use App\Import;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use SuperClosure\Serializer;
use App\Jobs\APIAIAgentUpdater;
use Exception;


class CSVReader implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, DispatchesJobs;

    protected $import;
    protected $category_id;
    protected $user;

//    public $import_id;
//    public $import_log;
//    public $collection;

    /**
     * CSVReader constructor.
     * @param Import $import
     */
    public function __construct(Import $import, $category_id, $user)
    {
        $this->import = $import;
        $this->category_id = $category_id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->openCSVFile();
        $this->parseToKeyValueArray($path, $this->import, $this->category_id);
    }

    /**
     * @return mixed
     */
    public function openCSVFile() {
//        Log::info("Reading file: " . 'app/'.$this->import->csv_path);
        $path = Storage::url('app/'.$this->import->csv_path);
        return $path;
    }

    public function deleteSCVFile() {
//        Storage::delete('storage/app/'.$this->import->csv_path);
        $filename = base_path() . '/storage/app/'.$this->import->csv_path;
//        Log::info("Deleting CSV file: " . $filename);
        unlink($filename);
    }

    /**
     * @param $file
     * @return mixed
     */
    public function parseToKeyValueArray($file, $import, $category_id)
    {
        /*
         * implement error handling!
         */
        try {
            Excel::filter('chunk')->load($file)->chunk(100, function($results) use($import, $category_id) {

                foreach ($results as $row) {
                     dispatch(new DatabaseWriter($this->user, $import, $row, $category_id));
                }
                
                unset($results); //Clear result value for memory release

            }, false);

            $this->deleteSCVFile();

            $agent = Agents::find($import->agent_id);
            if ($agent) {
                Agents::setTrainingStatus(config('agent.training.needed'), $agent);

                event(
                    new BroadcastTrainingStatus(
                        [
                            'status' => Agents::getTrainingStatus($agent)
                        ],
                        $this->user
                    )
                );
            }

            return true;
        }
        catch(\PHPExcel_Reader_Exception $e){
            return false;
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('CSVReader Job failed due to ' . $exception->getMessage());
    }

}
