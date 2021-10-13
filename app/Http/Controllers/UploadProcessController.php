<?php

namespace App\Http\Controllers;

use App\Agents;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Response;
use App\Import;
use App\Jobs\CSVReader;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Bus\Queueable;

class UploadProcessController  extends Controller
{
    use DispatchesJobs, Queueable;

    protected $image;
    protected $user;

    /*
     * Construct method call ImageController instance for upload the images
     *
     */
    public function __construct(ImageController $imageController) {
        $this->image = $imageController;
    }

    /*
     * Image upload view
     */
    public function imageUpload() {
        $all_files = $this->image->callback_all_images();
        return view("upload.image")->with(array("all_files"=>$all_files));
    }

    /*
     * Face upload post ajax request
     */
    public function storeImage( Request $request ) {
        $file = $request->file("file");
        $response = $this->image->upload($file);
        return Response::json( $response );
    }

    /**
     * @return $this
     */
    public function csvUpload() {
        $data = array("title"=>"Title");
//        $path = Storage::url("app/2/csv/1490198536_eShoppingBD-csv - Sheet1.csv");
        return view("upload.csvupload")->with($data);
	}

    /**
     * Upload csv view
     * @param Request $request
     * @return mixed
     */
    public function csv_upload_process(Request $request) {

        try {

            $category_id = ($request->input('category_id')) ? $request->input('category_id') : null;
            $category = Category::find($category_id);

            if ($category == null) {
                return [
                    'error' => true,
                    'message' => "You must select a valid category first!"
                ];
            }

            $import = $this->getCSVFileInStorage($request);

            $this->dispatch(
                (new CSVReader($import, $category_id, $this->user))
                    ->onQueue(
                        config('queueNames.csv_reader')
                    )
            );

            return array('import_id'=>$import->id,'error'=>false, 'message'=>'CSV Uploaded');
        }
        catch( Exception $e ){
            return array(
                'error'=>true,
                'message'=> $e->getMessage()
            );
        }
    }

    /**
     * Writes file to server under user.id/bot_id/csv
     * @param $request
     * @return Import
     */
    private function getCSVFileInStorage($request) {
        $this->user = Auth::user();
        $agent_code = Redis::get('agent_code_'.$this->user->id);
        $current_agent = Agents::where('agent_code', $agent_code )->first();

        $import = new Import();

        $csv                = $request->file("csv_file");
        $full_name          = $csv->getClientOriginalName();
        $filter_name        = time()."_".$full_name;
        $csv_path           = $this->user->id . $filter_name;
        $import->csv_name   = $filter_name;
        $import->state      = "uploading";
        $import->csv_path   = $csv_path;
        $import->agent_id   = $current_agent->id;
        $import->state      = "upload process";
        $import->save();
        Storage::put( $csv_path, file_get_contents( $csv ));
        return $import;
    }
}
