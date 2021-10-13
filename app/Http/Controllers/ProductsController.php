<?php

namespace App\Http\Controllers;
use App\Agents;
use App\AgentWhitelistedDomains;
use App\FacebookAPI;
use App\Jobs\ProcessFacebookPagePost;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use League\Flysystem\File;
use Validator;
use App\Category;
use App\Products;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Storage;


class ProductsController extends Controller
{
    use DispatchesJobs, Queueable;

    /**
     * ProductsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('products.products');
    }

    private function assignCategoryNameInProductsList($products) {
        $category = null;
        if( count($products) > 0 ) {
            foreach( $products as $product ) {

                if ($category == null || $category->id != $product->category_id) {
                    $category = $product->category;
                }

                if ($category) {
                    $product->category_name = $category->name;
                }
                else
                    $product->category_name = '';
            }
        }
    }

    public function uncategorizedProducts(Request $request) {
        try {
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $agent_id = Agents::where('agent_code', $agent_code)->value('id');

            $uncategorized_products = Products::where([
                'agent_id' => $agent_id,
                'flag' => config('agent.flag.uncategorized')
            ])
                ->orderBy('code', 'asc')
                ->get();

            $response = array(
                'error' => false,
                'uncategorized_products' => $uncategorized_products
            );

            return $response;
        }
        catch(Exception $e ) {
            $response = array('error'=>true, 'message'=>$e->getMessage());
            return $response;
        }
    }

    /*
     * Fetch all products
     * api/product_lists
     * return array
     */
    public function api_all_product(Request $request){
        try {
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $agent_id = Agents::where('agent_code', $agent_code)->value('id');
            $all_products = Products::where(['agent_id'=>$agent_id])
                ->where('flag', '!=', config('agent.flag.uncategorized'))
                ->where('flag', '!=', config('agent.flag.deleted'))
                ->orderBy('code', 'asc')
//                ->limit($session->limit)
//                ->offset($session->offset * $session->limit)
                ->get();

            $this->assignCategoryNameInProductsList($all_products);

            $categories = Category::allSelectableCategories();

            $response = array(
                'error' => false,
                'product_list' => $all_products,
                'categories' => $categories,
                'agent_code'=>$agent_code,
                'user_id'=>$user->id
            );
            return $response;

        }catch(Exception $e ) {
            $response = array('error'=>true, 'message'=>$e->getMessage());
            return $response;
        }
    }

    public function productsUnderCategory(Request $request) {
        try {
            $category_id = ($request->input('category_id')) ? $request->input('category_id') : null;

            if ($category_id == null) {
                throw new Exception('Must provide category_id');
            }

            $category = Category::find($category_id);

            if ($category == null) {
                throw new Exception('Could not find a category with given category_id');
            }

            $products = $category->products()
                ->where('flag', '!=', config('agent.flag.uncategorized'))
                ->where('flag', '!=', config('agent.flag.deleted'))
                ->get();

            $this->assignCategoryNameInProductsList($products);

            $response = array(
                'error' => false,
                'product_list' => $products
            );
            return $response;

        }catch(Exception $e ) {
            $response = array(
                'error'=>true,
                'message'=>$e->getMessage());
            return $response;
        }
    }

    /*
     * Update view
     * @return product view with old data
     */
    public function update_view( Request $request ){

        $user = Auth::user();
        $agent_code = Redis::get('agent_code_'.$user->id);
        $product_id = $request->product_id;
        $product = Products::find( $product_id );
        $required_attributes = ($product->category->required_attributes != null ) ? explode(",",$product->category->required_attributes) : [] ;
        $current_attribute_and_values =  ($product->product_attributes) ? json_decode($product->product_attributes, true) : [] ;

        $product->category_required_attributes = $this->mixingCategoryAndProductAttributes($required_attributes, $current_attribute_and_values);
        $categories = Category::allSelectableCategories();

        return view("products.update")->with(['product'=>$product,'categories'=>$categories,'agent_code'=>$agent_code]);
    }


    /*
     * Product Create Page
     * @return product.view
     */
    public function create() {
        $categories = Category::allSelectableCategories();
        return view("products.create")->with(array('categories'=>$categories));
    }

    public function patchProduct(Request $request) {
        $product = Products::find($request->id);
        if (!isset($product)) {
            return [
                'error' => true,
                'id' => $request->id,
                'message' => "Entity update failed! Entity with given id not found."
            ];
        }

        $validator = Validator::make($request->all(), [
            'stock' => 'required|numeric|max:2147483647'
        ]);

        if ($validator->fails()) {
            return [
                'error'=>true,
                'id' => $request->id,
                'message'=> "Invalid input!"
            ];
        }

        $product->stock = $request->input('stock');
        $product->save();
        $this->assignCategoryNameInProductsList([$product]);
        
        return ['error'=>false, 'product'=> $product];
    }

    /*
     * Update form process
     */
    public function update( Request $request ){
        try {
            $product = Products::find($request->input('id'));

            if($request->input('postOnFacebook')) {

                $action = $request->input('postOnFacebook');
//                Log::info("postOnFacebook: " . $action);

                if ($action == 'Post on Facebook') {
                    $sRes = $this->postOnFacebook($product);

                    if (isset($sRes['data']['error']['message'])) {
                        Session::flash('error', $sRes['data']['error']['message']);
                    }
                    else if (isset($sRes['error']) && $sRes['error'] == true) {
                        Session::flash('error', $sRes['message']);
                    }
                    else {
                        Session::flash('success', 'Successfully posted ' . $product->name . ' on your Facebook page.');
                    }
                }
                elseif ($action == "Remove from Facebook") {
                    $agent = Agents::getCurrentAgent();
                    if (!$agent) {
                        throw new Exception("Authentication error!");
                    }
                    elseif(!$agent->page_subscription) {
                        throw new Exception("Please connect your facebook page before removing this post from facebook.");
                    }

                    $postID = json_decode($product->social_posts)->id;

                    Log::info("post ID: " . $postID);

                    $sRes = FacebookAPI::deleteFacebookPost($postID, $agent);

                    if (isset($sRes['data']['error']['message'])) {
                        throw new Exception($sRes['data']['error']['message']);
                    }
                    else if (isset($sRes['error']) && $sRes['error'] == true) {
                        throw new Exception($sRes['message']);
                    }
                    else {
                        $product->social_posts = null;
                        $product->save();
                        Session::flash('success', 'Successfully deleted ' . $product->name . ' post from your Facebook page.');
                    }
                }

                return back()->withInput();
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required',//|min:3|max:80
//                'detail' => 'max:80',
                'code' => 'required',
                'price' => 'required|numeric',
                'offer_price' => 'numeric',
                'stock' => 'required|numeric',
                'unit' => 'required',
                'external_link'=> 'nullable|url'
            ]);

            $validator->after(function ($validator) use ($request, $product) {
                /*if (preg_match("/[\'^£$%&*()}{@#~?><>,|=_+-]/", $request->input('name')) == 1) {//+¬-
                    $validator->errors()->add('name', 'Name may only contain letters and numbers!');
                }*/

                $uniqueCode = Products::where('id', '!=', $product->id)
                    ->where('agent_id', '=', $product->agent_id)
                    ->where('code', '=', $request->input('code'))
                    ->where('flag', '!=', config('agent.flag.deleted'))
                    ->count();

                if( $uniqueCode > 0 )
                    $validator->errors()->add('code', 'You already have a product with this code!');
            });

            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            $category_id = $request->input('category_id');


            if ($product->category_id != $category_id) {

                // For uncategorized products back into a category => just set flag to created
                if ($product->flag == config('agent.flag.uncategorized')) {
                    $product->flag = config('agent.flag.created');
                }
                // Create new product and set created if category changed
                else {
                    $product->flag = config('agent.flag.deleted');
                    $product->save();

                    $product = $product->replicate();
                    $product->flag = config('agent.flag.created');
                    $product->category_id = $category_id;
                    $product->save();
                }

                Agents::setTrainingStatus(config('agent.training.needed'));
                Agents::actionOnTrainingBroadCast();

            }

            /*
             * check if new image upload
             * Delete previous image if exists
             *
             */
            if($request->file('file')) {
                if( $product->is_image )
                    $this->deleteProductImage( $product->is_image );

                $image_path = $this->moveProductImage( $request->file('file'), $request->input('code'));
                if( $image_path!= false )
                    $product->is_image = $image_path;

            }elseif( $product->code != $request->input('code')) {
                    $product->is_image = $this->renameProductImage( $product->is_image, $request->input('code'));
            }

            if($product->is_image === null ) {
                Session::flash('empty_image', 'Please upload an entity image!');
            }

            $name = $request->input('name');
            $code = $request->input('code');

            if ( $product->flag !== config('agent.flag.created')
                &&
                ( $product->name !== $name || $product->code !== $code )
            ) {
                $product->flag = config('agent.flag.updated');

                Agents::setTrainingStatus(config('agent.training.needed'));
                Agents::actionOnTrainingBroadCast();
            }

            $attributes = ($request->input("attribute_list") != null && count($request->input('attribute_list')) > 0) ?
                json_encode($request->input('attribute_list')) : null;
            $product->product_attributes = $attributes;
            $product->name = $name;
            $product->detail = $request->input('detail');
            $product->code = $code;
            $product->price = $request->input('price');
            $product->offer_price = $request->input('offer_price');
            $product->unit = $request->input('unit');
            $product->stock = $request->input('stock');
            $product->priority = $request->input('priority');
            $product->category_id = $request->input('category_id');
            $product->external_link = $request->input('external_link') ? $request->input('external_link') : null;
            $product->image_link = ($request->input('image_link')) ? $request->input('image_link') : null ;

            if ($product->save())
                Session::flash('success', 'Entity update successful!');

            return back()->withInput();

        }catch(Exception $e ){
            Log::info($e);
            Session::flash('error', $e->getMessage());
            return back()->withInput();
        }
    }

    /*
     * Store new products
     * @return null
     *
     */
    public function product_store(Request $request){

        try {
            $product = new Products();
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $agent_id = Agents::where('agent_code', $agent_code)->value('id');

            $validator = Validator::make($request->all(), [
                'name' => 'required',//|min:3|max:80
//                'detail' => 'max:80',
                'code' => 'required',
                'price' => 'required|numeric',
                'offer_price' => 'numeric',
                'stock' => 'required|numeric',
                'category_id' => 'required|numeric',
                'unit' => 'required',
                'image_link'=> 'nullable|url',
                'external_link'=> 'nullable|url'
            ]);

            $validator->after(function ($validator) use ($request, $agent_id) {
                /*if (preg_match("/[\'^£$%&*()}{@#~?><>,|=_+-]/", $request->input('name')) == 1) {//+¬-
                    $validator->errors()->add('name', 'Name may only contain letters and numbers!');
                }*/

                $uniqueCode = Products::where([
                    'code'=>$request->input('code'),
                    'agent_id'=>$agent_id
                ])
                    ->where('flag', '!=', config('agent.flag.deleted'))
                    ->count();

                if( $uniqueCode > 0 )
                    $validator->errors()->add('code', 'You already have a product with this code!');

                
            });

            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }

            if($request->file('file')){
                $image_path = $this->moveProductImage( $request->file('file'), $request->input('code'));
                if( $image_path!= false)
                    $product->is_image = $image_path;
            }else {
                Session::flash('empty_image', 'Please upload an entity image!');
            }

            $attribute_list = $request->input('attribute_list');
            $attributes = ( isset($attribute_list) && count($attribute_list) > 0 ) ?
                json_encode($request->input('attribute_list')) : null;
            $product->product_attributes = $attributes;
            $product->category_id = $request->input('category_id');
            $product->agent_id = $agent_id;
            $product->name = $request->input('name');
            $product->code = $request->input('code');
            $product->detail = $request->input('detail');
            $product->price = $request->input('price');
            $product->offer_price = $request->input('offer_price');
            $product->stock = $request->input('stock');
            $product->priority = $request->input('priority');
            $product->unit = $request->input('unit');

            $product->image_link = ($request->input('image_link')) ? $request->input('image_link') : null ;
            $product->external_link = ($request->input('external_link')) ? $request->input('external_link') : null ;

            $product->flag = config('agent.flag.created');

            Agents::setTrainingStatus(config('agent.training.needed'));
            Agents::actionOnTrainingBroadCast();

//            $this->postOnFacebook($product, $agent_id);

            if ($product->save())
                Session::flash('success', 'Entity create successful!');

            return redirect('/'.$agent_code.'/products/'.$product->id);

        }catch( Exception $e ){
            return response()->view('errors.403', array('message'=>$e->getMessage()), 403);
        }
    }

    /*
     * Delete product and remove the image if there exists
     */
    public function delete_product( Request $request ){

        try {
            $product_ids = \GuzzleHttp\json_decode($request->input('product_ids'));

            $products = Products::whereIn('id', $product_ids )->get();

            if (count($products) > 0) {

                $trainingRequired = false;

                $image_path = [];

                foreach ($products as $product) {

                    if ($product->is_image) {
                        $image_path[] = $this->deleteProductImage($product->is_image);
                    }

                    if ($product->flag === config('agent.flag.uncategorized')) {
                        $product->delete();
                    }
                    else {
                        $product->flag = config('agent.flag.deleted');
                        $product->save();

                        if ($trainingRequired == false) $trainingRequired = true;
                    }
                }

                if ($trainingRequired === true) {
                    Agents::setTrainingStatus(config('agent.training.needed'));
                    Agents::actionOnTrainingBroadCast();
                }
            }

            return array('error'=>false,'image_path'=>$image_path,'product'=>$products);

        }catch (Exception $ex ){
            $response = array('error'=>true,'message'=>$ex->getMessage());
            return $response;
        }

    }

    /*
     * Move uploaded product to products directory
     * @return boolean
     * @params FILE, code
     */
    private function moveProductImage( $file, $code ){
        $user = Auth::user();
        $image_name = $file->getClientOriginalName();
        $extension = explode('.', $image_name);
        $extension = end($extension);
        $filter_name = str_replace(' ','_',$code) . '.' . $extension;
        $image_path = $user->id . '/' . Redis::get('agent_code_'.$user->id) . '/products/'.$filter_name;
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
            if(Storage::disk('uploads')->delete($image_path));
            return true;
        }

        return false;

    }

    /*
     * Rename Image name with product code
     * @return boolean
     * @params PATH, code
     */
    /**
     * @param $old_code
     * @param $new_code
     * @return mixed
     */
    private function renameProductImage($image_path, $new_code ){
        $user = Auth::user();
        $extension = explode('.', $image_path);
        $extension = end($extension);
        $new_name = str_replace(' ','_', $new_code ). '.' . $extension;
        $base_path = $user->id . '/' . Redis::get('agent_code_'.$user->id) . '/products/';
        Log::info("image exists: ". $image_path);
        if( Storage::disk('uploads')->exists($image_path) == true )
        {
            Log::info("new path-1 : ". $base_path.$new_name);
            if(Storage::disk('uploads')->move($image_path , $base_path.$new_name)) {
                Log::info("new path-2 : ". $base_path.$new_name);
                return $base_path . $new_name;
            }
        }
        return null;

    }

    /*
     * Get category attributes and product attributes with ID's
     * @args category_id, product_id
     * @return Json Objects
     *
     * Ajax Request
     */
    public function getCategoryAndProductAttributes( Request $request ){

        try {
            $categoryId = $request->input('cat_id');
            $productId = $request->input('product_id');
            $process = $request->input('process');
            $category = Category::find($categoryId);

            $required_attributes = ($category->required_attributes == null) ? [] : explode(',', $category->required_attributes);
            $absoluteAttributeFormat = null ;

            if( $process == 'update' ) {
                $product = Products::find( $productId );
                $current_attributes_and_values = ($product->product_attributes) ? json_decode($product->product_attributes, true) : [] ;

                $absoluteAttributeFormat = $this->mixingCategoryAndProductAttributes( $required_attributes, $current_attributes_and_values );

            }else{
                if ($required_attributes !== null) {
                    foreach ($required_attributes as $attr) {
                        $absoluteAttributeFormat[$attr] = ucfirst($attr);
                    }
                }
            }

            $result = array('error'=>false, 'attributes'=>$absoluteAttributeFormat, 'process'=>$process );
        }catch ( Exception $e ){
            $result = array('error'=>true, 'message'=>$e->getMessage() );
        }

        return $result;

    }

    /*
     * Mixing product attributes and categories
     */
    private function mixingCategoryAndProductAttributes($required_attributes, $current_attribute_and_values) {
        $current_attribute_name = array_keys( $current_attribute_and_values);
        $category_required_attributes = [];
        for( $i=0; $i < count( $required_attributes ); $i++ ){
            if(isset($current_attribute_name[$i]) && $current_attribute_name[$i] ==  $required_attributes[$i]){
                $category_required_attributes[$required_attributes[$i]] = $current_attribute_and_values[$current_attribute_name[$i]];
            }else {
                $category_required_attributes[$required_attributes[$i]] = '';
            }
        }

        return $category_required_attributes;
    }

    public function postProductsOnFacebook(Request $request) {
        try {
            $agent = Agents::getCurrentAgent();
            if( $agent == null )
                throw new Exception("Authentication error!");
            elseif(!$agent->page_subscription)
                throw new Exception("Please connect your facebook page before posting on facebook.");

//            Log::info("Request: " . print_r($request->all(), true));

            $product_ids = json_decode($request->input('product_ids'));
            $message = $request->input('message');

            if (!$product_ids || !$message)
                throw new Exception("Invalid argument error!");

            $products = [];
            foreach($product_ids as $product_id) {
                $product = Products::find($product_id);

                if (!$product->is_image && !$product->image_link) {
                    throw new Exception("Not all of your selected entities have image available. Please try with image available for all entities.");
                }

                array_push($products, $product);
            }

            $this->dispatch(
                (new ProcessFacebookPagePost($agent, $message, $products))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );

            return [
                'error' => false,
                'message' => "Your entities are being posted on facebook, please check your page in a moment."
            ];
        }catch(Exception $e) {
            return ['error'=>true, 'message'=>$e->getMessage() ];
        }
    }

    private function postOnFacebook($product) {
        try {
            $agent = Agents::getCurrentAgent();
            if (!$agent) {
                throw new Exception("Authentication error!");
            }
            elseif(!$agent->page_subscription) {
                throw new Exception("Please connect your facebook page before posting on facebook.");
            }

            if ($product->social_posts != null) return [
                'data' => [
                    'error' => [
                        'message' => $product->name . ' already posted on Facebook!'
                    ]
                ]
            ];

            $url = null;
            if ($product->image_link) $url = $product->image_link;
            elseif ($product->is_image) $url = config('agent.base_url') . '/uploads/' . $product->is_image;

            $msg = $product->name
                . PHP_EOL
                . 'Code: ' . $product->code
                . PHP_EOL
                . 'Price: BDT ' . $product->offer_price
                . PHP_EOL
                . $product->detail;

            if ($url) {
                $sRes = FacebookAPI::postPhotoAsPage(
                    [
                        'message' => $msg,
                        'url' => $url,
                    ],
                    $agent);
            }
            else {
                $sRes = FacebookAPI::postFeedAsPage(
                    [
                        'message' => $msg
                    ],
                    $agent);
            }

            Log::info("Facebook post response: " . print_r($sRes, true));

            if ($sRes['error'] == false && isset($sRes['data']['id'])) {
                $sRes['data']['sync_status'] = 1;
                $product->social_posts = json_encode($sRes['data']);
                $product->save();
            }
            elseif (!isset($sRes['data']['error'])) {
                $sRes = [
                    'error'=>true,
                    'message'=> "Could not post at the moemnt! Please contact developer!"
                ];
            }

            return $sRes;
        } catch(Exception $e) {
            return [
                'error'=>true,
                'message'=>$e->getMessage()
            ];
        }
    }

    /*
     * Dynamic CSV generate by category ID
     */
    public function dynamic_csv( Request $request ){
        try {

            $category_id = $request->input('category_id');
            if (!$category_id) {
                throw new Exception("Invalid category_id");
            }

            $category = Category::find($request->input('category_id'));
            if (!$category) {
                throw new Exception("Cannot find a category with category_id = " . $category_id);
            }

            $csvData = [
                'name',
                'code',
            ];

            if ($category->required_attributes) {
                $attArray = explode(',', $category->required_attributes);
                $csvData = array_merge($csvData, $attArray);
            }

            array_push($csvData,
                'price',
                'offer_price',
                'priority',
                'detail',
                'unit',
                'stock',
                'image_link',
                'external_link');

            Log::info($csvData);

            $filename = public_path() . '/products_' . $category->agent_id . '.csv';
            $file = fopen($filename, 'w');
            fputcsv($file, $csvData);
            fclose($file);

            Log::info('Download request: ' . $filename);

            return response()->download($filename)->deleteFileAfterSend(true);

        }catch( Exception $e ){
            return ['error'=>true, 'message'=>$e->getMessage() ];
        }
    }
}
