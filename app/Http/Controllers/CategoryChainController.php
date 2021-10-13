<?php

namespace App\Http\Controllers;

use App\AgentWhitelistedDomains;
use App\Jobs\TransferProducts;
use App\Products;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Jobs\APIAIIntentAPI;
use App\Agents;
use App\Category;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;
use phpseclib\System\SSH\Agent;
use Illuminate\Support\Facades\DB;

class CategoryChainController extends Controller
{
    use DispatchesJobs, Queueable;

    /**
     * CategoryChainController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        return view('categories.create');
    }

    /*
     * Get all category by chainlist
     */
    public function getAllCategory(){
        try {
            $categories = Category::allSelectableCategories();
            $response = array('error'=>false, 'category_list'=>$categories);
            return $response;
        }catch(Exception $ex ){
            $response = array('error'=>true, 'message'=>$ex->getMessage());
            return $response;
        }
    }


    /*
     * Get all child categories for parent with id
     * 1. Call api ai for get the default category and insert into category table
     * 2. Call Database for get the current category lists
     */
    public function getCategoryListByParent(Request $request){

        $parentId = $request->input('parent_id');

        if ($parentId < 0 && $parentId !== 'null') {
            return array('error'=>true, 'message'=> "Invalid id sent in request!");
        }

        try {
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $active_agent = Agents::where('agent_code', $agent_code)->first();

            $categories = Category::getChildCategoriesForParent($parentId, $active_agent);

//            Log::info("Returned: " . print_r($categories, true));

            $response = [
                'error' => false,
                'category_list' => $categories
            ];

            return $response;
        }
        catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /*
     * Get the category
     */
    public function save_category( Request $request ){
        try {
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_' . $user->id);
            $current_agent = Agents::where('agent_code', $agent_code)->first();

            if ($current_agent == null) {
                return [
                    'error' => true,
                    'message' => new Exception('Could not find specified agent.')
                ];
            }
            if ($current_agent->is_default_intents_fetched == false) {
                return [
                    'error' => true,
                    'message' => new Exception(
                        'Do not have root category reference yet. Please try refreshing the page.'
                    )
                ];
            }

            $category_id = ($request->input('cat_id')) ? $request->input('cat_id') : null;
            $parent_id = ($request->input('parent_id')) ? $request->input('parent_id') : null;
            $name = $request->input('name');
            $description = $request->input('description');
            $required_attributes = $request->input('required_attributes');
            $required_attributes = (
                $required_attributes != null
                &&
                strlen($required_attributes) > 0
            ) ?
                strtolower($required_attributes) : null;

            $file = $request->file('file');
            $image_path = null;
            if ($category_id) {
                $category = Category::find($category_id);

                if ($category == null) {
                    return [
                        'error' => true,
                        'message' => new Exception('Could not find specified category.')
                    ];
                }

            } else {
                if ($name) {
                    $category = Category::where(['name' => $name])
                        ->where('flag', '!=', config('agent.flag.deleted'))
                        ->where('agent_id', '=', $current_agent->id)
                        ->get()
                        ->first();

                    if ($category != null) {
                        return [
                            'error'     => true,
                            'message'   => 'Category with this name already exists!'
                        ];
                    }
                }

                $category = new Category();
                $category->prev = $parent_id;
                $category->agent_id = $current_agent->id;

                $category->flag = config('agent.flag.created');

                Agents::setTrainingStatus(config('agent.training.needed'), $current_agent);
                Agents::actionOnTrainingBroadCast();
            }

            $check_img_delete = null;
            if ($file != null) {
                if( $category->image !== null )
                    $this->deleteCategoryImage( $category->image );
                $image_path = $this->moveCategoryImage( $file );
            }elseif( $category_id != null ) {
                $is_image_delete = (bool)$request->input('is_image_delete');
                if( $is_image_delete == true ) {
                    $check_img_delete = 1;
                    $this->deleteCategoryImage($category->image);
                    $image_path = null;
                }else {
                    $image_path = $category->image;
                }
            }

            if ( $name != null && strcmp($category->name, $name) !== 0 ) {
                Products::updateFlag(config('agent.flag.ok'), config('agent.flag.updated'), $category);
            }

            $prevSynonyms = json_decode($category->synonyms);
            if ($prevSynonyms) sort($prevSynonyms);
            $newSynonyms = json_decode($request->input('synonyms'));
            if ($newSynonyms) sort($newSynonyms);
            $isSameSynonyms = ($prevSynonyms == $newSynonyms);

            $prevResponse = json_decode($category->text_response);
            if ($prevResponse) sort($prevResponse);
            $newResponse = json_decode($request->input('text_response'));
            if ($newResponse) sort($newResponse);
            $isSameResponse = ($prevResponse == $newResponse);

            $is_rss_feed = $request->input('rss_feed') == 'true' ? true : false;

            if ($category->flag !== config('agent.flag.created')
            &&
                $category->flag !== config('agent.flag.updated')
            &&
                (
                    ( $name != null && strcmp($category->name, $name) !== 0 )
                    ||
                    ( $description != null && strcmp($category->description, $description) !== 0 )
                    ||
                    (
                        $required_attributes != null
                        &&
                        strcmp($category->required_attributes, $required_attributes) !== 0
                    )
                    ||
                    ( $image_path != null && strcmp($category->image, $image_path) !== 0 )
                    ||
                    ($image_path == null && $category->image != null)
                    ||
                    (strcmp($category->external_link, $request->input('external_link')) !== 0)
                    ||
                    ($isSameSynonyms == false)
                    ||
                    ($isSameResponse == false)
                    ||
                    $category->rss_feed != $is_rss_feed
                )
            ) {
                $category->flag = config('agent.flag.updated');

                Agents::setTrainingStatus(config('agent.training.needed'), $current_agent);
                Agents::actionOnTrainingBroadCast();
            }

            $category->name = $name;
            $category->description = $description;
            $category->required_attributes = $required_attributes;
            $category->image = $image_path;
            $category->external_link = $request->input('external_link') ? $request->input('external_link') : null;
            $category->synonyms = $newSynonyms ? $request->input('synonyms') : null;
            $category->text_response = $newResponse ? $request->input('text_response') : null;
            $category->rss_feed = $is_rss_feed;

            $category->save();

            $category->has_subcategory = $category->hasSubCategory();
            $category->product_count = $category->activeProductsCount();

            $parentCat = $parent_id ? Category::find($parent_id) : null;
            if ($parentCat) {
                if ($parentCat->rss_feed == true) {
                    $parentCat->rss_feed = false;
                    $parentCat->external_link = null;

                    $parentCat->save();
                }

                $parentCat->has_subcategory = $parentCat->hasSubCategory();
            }

            return  [
                'error' => false,
                'category' => $category,
                'parent_category' => $parentCat,
                'is_img_delete'=>$check_img_delete,
                "res"=> $request->all()
            ];

        }catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /*
    * Move uploaded product to products directory
    * @return boolean
    * @params FILE, code
    */
    private function moveCategoryImage( $file  ){
        $user = Auth::user();
        $image_name = $file->getClientOriginalName();
        $extension = explode('.', $image_name);
        $extension = end($extension);
        $filter_name = time() . '.' . $extension;
        $image_path = $user->id . '/' . Redis::get('agent_code_'.$user->id) . '/categories/'.$filter_name;
        if(Storage::disk('uploads')->put($image_path, file_get_contents($file))) {
            return  $image_path;

        }

        return false;

    }

    /*
    * Delete produdct image from folder after delete the product
    */
    private function deleteCategoryImage( $image_path ){

        if( Storage::disk('uploads')->exists($image_path) == true )
        {
            if(Storage::disk('uploads')->delete($image_path));
            return true;
        }

        return false;

    }


    private function cacheToBeDeletedCategories($data) {
        $user = Auth::user();
        $agent_code = Redis::get('agent_code_'.$user->id);
        $current_agent = Agents::where('agent_code', $agent_code )->first();

        $key = $user->id.'_'.$current_agent->id.'_'.'ToBeDeletedCategories';
//        Log::info('Key: ' . $key);

        $deleteOperationHistory = Redis::get($key);

//        Log::info('Redis History: ');
//        Log::info($deleteOperationHistory);

        if ($deleteOperationHistory == FALSE) {
            Log::info('Setting new data in redis');
            Redis::setEx($key, 300, json_encode([$data]));
        }
        else {
            Log::info('Appending new data in redis');

            $historyArray = json_decode($deleteOperationHistory, true);

            if (count($historyArray) > 2) {
                array_pop($historyArray);
            }
            array_push($historyArray, $data);

            Log::info('Redis cache count: ' . count($historyArray));

            Redis::setEx($key, 300, json_encode($historyArray));
        }
    }

    private $orphanProducts = [];

    private function getAllProductsRecursive($category) {

        $categories = $category->children_recursive;
        if (count($categories) > 0) {
            foreach ($categories as $child) {
                $this->getAllProductsRecursive($child);
            }
        }

        $products = $category->products()->get()->toArray();

        if (count($products) > 0) {
            $this->orphanProducts = array_merge($this->orphanProducts, $products);
            Log::info('Products count: ' . count($this->orphanProducts) . ' added from: ' . $category->name);
        }
    }

    private $fallback = true;

    public function beforeDeleteOperation(Request $request) {
        $category_id = ($request->input('cat_id')) ? $request->input('cat_id') : null;

        if ($category_id != null) {
            return $this->prepareDeleteOperationData($category_id, true, true);
        }
        else {
            return ['error'=>true, 'message'=>"Invalid request"];
        }
    }

    private function prepareDeleteOperationData($category_id, $should_cache, $is_api_res) {
        try {
            // Try to retrive from redis cache
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $current_agent = Agents::where('agent_code', $agent_code )->first();

            $key = $user->id.'_'.$current_agent->id.'_'.'ToBeDeletedCategories';

            $deleteOperationHistory = Redis::get($key);
            $this->fallback = true;

            if ($deleteOperationHistory != FALSE) {
                $requiredData = null;
                $historyArray = json_decode($deleteOperationHistory, true);

                foreach($historyArray as $data) {

                    if ( isset($data[$category_id]) ) {
                        Log::info('Key exists in redis cache');
                        $requiredData = $data[$category_id];
                        break;
                    }
                }
                if ($requiredData != null) {
                    Log::info('Found in redis cache...');
                    $this->fallback = false;

                    if ($is_api_res == true) {
                        $delProductsCount = count($requiredData['products']);
                        $message = $delProductsCount . ' uncategorized products will be transfered to:';

                        $data = [
                            'error'=>false,
                            'data'=> [
                                'message'           => $message,
                                'delProductsCount'  => $delProductsCount,
                                'delCategory'       => $requiredData['delCategory'],
                                'parentAndSiblings'   => $requiredData['parentAndSiblings']
                            ]
                        ];

                        return $data;
                    }
                    else {
                        return $requiredData;
                    }
                }
            }

            //Fallback to DB
            if ($this->fallback == true) {
                Log::info('Fallback to DB fetch...');

                $delCategory = Category::find($category_id);

                if ($delCategory != null) {

                    $categories = $delCategory
                        ->childrenRecursive()
                        ->where('flag', '!=', config('agent.flag.deleted'))
                        ->get();
                    $delCategory->children_recursive = $categories;

                    $parentAndSiblings = Category::find($delCategory->prev);
                    $parentAndSiblings->children = $parentAndSiblings
                        ->children()
                        ->where('id', '!=', $delCategory->id)
                        ->where('flag', '!=', config('agent.flag.deleted'))
                        ->get();

                    $this->orphanProducts = array();
                    $this->getAllProductsRecursive($delCategory);
                    Log::info('Orphan products cont: ' . count($this->orphanProducts));

                    $data = null;

                    if ($is_api_res == true) {
                        $delProductsCount = count($this->orphanProducts);

                        $message = $delProductsCount . ' uncategorized products will be transferred to:';

                        $data = [
                            'error'=>false,
                            'data'=> [
                                'message'           => $message,
                                'delProductsCount'  => $delProductsCount,
                                'delCategory'       => $delCategory,
                                'parentAndSiblings' => $parentAndSiblings
                            ]
                        ];
                    }
                    else {
                        $data = [
                            'products'          => $this->orphanProducts,
                            'delCategory'       => $delCategory,
                            'parentAndSiblings' => $parentAndSiblings
                        ];
                    }

                    if($should_cache == true) {
                        $this->cacheToBeDeletedCategories([
                            $category_id => [
                                'products'          => $this->orphanProducts,
                                'delCategory'       => $delCategory,
                                'parentAndSiblings' => $parentAndSiblings
                            ]
                        ]);
                    }

                    return $data;
                }
                else {
                    return ['error'=>true, 'message'=>"Invalid request"];
                }
            }
        } catch( Exception $e ){
            return ['error'=>true, 'message'=>$e->getMessage()];
        }
    }

    private function setCategoryDeleteFlagRecursive($categoryData) {
        $category = Category::find($categoryData['id']);

        if ($category != null) {
            $category->flag = config('agent.flag.deleted');
            $category->save();
        }

        $categories = $categoryData['children_recursive'];
        if (count($categories) > 0) {
            foreach ($categories as $child) {
                $this->setCategoryDeleteFlagRecursive($child);
            }
        }
    }

    public function transferAndDelete(Request $request) {
        try {
            $category_id = ($request->input('del_category_id')) ? $request->input('del_category_id') : null;
            $transfer_cat_id = ($request->input('transfer_category_id')) ? $request->input('transfer_category_id') : null;

            $data = $this->prepareDeleteOperationData($category_id, false, false);

            if ( isset($data['delCategory']) ) {
                $delCategory = $data['delCategory'];
                $this->setCategoryDeleteFlagRecursive($delCategory);

                Agents::setTrainingStatus(config('agent.training.needed'));
                Agents::actionOnTrainingBroadCast();
            }

            if ( isset($data['products']) ) {
                $products = $data['products'];

                if (count($products) > 0) {
                    $queueName = config('queueNames.transfer_products');
                    $this->dispatch((new TransferProducts($products, $transfer_cat_id))->onQueue($queueName));
                }
            }

            return ['error'=>false];

        } catch( Exception $e ){
            return ['error'=>true, 'message'=>$e->getMessage()];
        }
    }
}
