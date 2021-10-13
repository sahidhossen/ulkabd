<?php

namespace App\Jobs;

use App\Category;
use App\ErrorLog;
use App\Products;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use App\User;

class DatabaseWriter
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $csv_row;
    protected $agent_id;
    protected $user;
    protected $import;
    protected $category_id;

    /**
     * DatabaseWriter constructor.
     * @param $import
     * @param $row
     */
    public function __construct( $user, $import, $row, $category_id )
    {
        $this->csv_row = $row;
        $this->import = $import;
        $this->agent_id = $import->agent_id;
        $this->user = $user;
        $this->category_id = $category_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            Log::info('validating product in csv row:');
            Log::info(print_r($this->csv_row, true));

//            Log::info('code length: ' . mb_strlen($this->csv_row->code));
//            Log::info('name length: ' . mb_strlen($this->csv_row->name));
//            Log::info('detail length: ' . mb_strlen($this->csv_row->detail));
//            Log::info('unit length: ' . mb_strlen($this->csv_row->unit));

            if(
                !isset($this->csv_row->code) || mb_strlen($this->csv_row->code) <= 0
                ||
                !isset($this->csv_row->name) /*|| mb_strlen($this->csv_row->name) > 40*/
                ||
                /*(isset($this->csv_row->detail) && mb_strlen($this->csv_row->detail) > 80)
                ||*/
                !isset($this->csv_row->price) || is_numeric($this->csv_row->price) === FALSE
                ||
                !isset($this->csv_row->offer_price) || is_numeric($this->csv_row->offer_price) === FALSE
                ||
                /*$this->csv_row->unit == null || mb_strlen($this->csv_row->unit) < 2 || mb_strlen($this->csv_row->unit) > 6
                ||*/
                !isset($this->csv_row->stock) || is_numeric($this->csv_row->stock) === FALSE
            ) {
                Log::info('---------Invalid product: ' . $this->csv_row->name);
//                $log_id = ErrorLog::write('csv:product', $this->csv_row);
//                $this->import->hard_errors =  ','.$log_id;
//                $this->import->save();

            }else {
                Log::info('------------------valid product:');
//                Log::info($this->csv_row);
                Products::processNewCSVRowData(
                    $this->user,
                    $this->csv_row,
                    $this->agent_id,
                    $this->category_id
                );
            }
        }
        catch(Exception $e) {
            Log::info( 'DatabaseWriter Exception: ' . $e->getMessage() );
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('DatabaseWriter Job failed due to ' . $exception->getMessage());
    }
}
