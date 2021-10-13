<?php

namespace App\Http\Controllers;

use App\Agents;
use App\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Redis;

class ImageController extends Controller
{
    /*
     * Upload images function
     * Check the product ID if exists
     * Move to the storage folder
     */
    public function upload( $file ) {
        try {
            $agent = Agents::getCurrentAgent();

            Log::info('Got agent');

            if ($agent === null) throw new Exception('Could not find target agent');

            $filename   = explode('.',$file->getClientOriginalName());
            $product_code = $filename[0];
            $extension = $file->getClientOriginalExtension();
            $size = $file->getClientSize();
            $image_name = $product_code.'.'.$extension;

            /*
             * Check if the product exists
             */
            $foundedProduct = Products::where('code', $product_code)
                ->where(['agent_id' => $agent->id])
                ->first();

            if( count($foundedProduct) < 1 ) {
                throw new Exception('Product not found for this image!');
            }

            /*
             * Store the image file in storage folder
             */
            $image_path = $this->moveProductImage($file, $foundedProduct->is_image );
            $foundedProduct->is_image = $image_path;
            $foundedProduct->save();

            $message = array('error'=>false, 'code'=>200);

            return $message;
        }
        catch( Exception $e ) {
            $message = array('error'=>true, 'code'=>400, 'file'=>$file, 'message'=>$e->getMessage(),'image'=>$image_name);
            return $message;
        }
    }

    /*
     * Get all images based on user and bot
     */
    public function callback_all_images(){
        $user = Auth::user();
        $full_paths = array();
        $files = Storage::allFiles($user->id.'/bot_1/');
        $directories = Storage::directories('/1/');

        foreach( $files as $file ){
            $url = Storage::url('file1.jpg');
        }
        return $files;
    }

    /*
     * Move uploaded product to products directory
     * @return boolean
     * @params FILE, code
     */
    private function moveProductImage( $file, $existing_image_path ){
        $user = Auth::user();
        $image_name = $file->getClientOriginalName();
        $image_path = $user->id . '/' . Redis::get('agent_code_'.$user->id) . '/products/'.$image_name;
        if($existing_image_path) {
            $this->deleteProductImage($existing_image_path);
        }
        if(Storage::disk('uploads')->put($image_path, file_get_contents($file))) {
            return  $image_path;
        }
        return false;

    }

    /*
    * Delete produdct image from folder after delete the product
    */
    private function deleteProductImage( $image_path ){
        if( Storage::disk('uploads')->exists($image_path) == true )
        {
            if(Storage::disk('uploads')->delete($image_path))
                return true;
        }
        return false;

    }
}
