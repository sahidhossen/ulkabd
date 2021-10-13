<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'agent_id',
        'name',
        'description',
        'text_response',
        'image' ,
        'next',
        'prev',
        'apiai_intent_id',
        'apiai_intent_name',
        'apiai_entity_id',
        'apiai_entity_name',
        'flag',
        'created_at',
        'updated_at',
        'external_link',
        'social_posts',
        'synonyms',
        'rss_feed'
    ];

    /*
     * Get a agents ID
     */
    public function agent()
    {
        return $this->belongsTo('App\Agents');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany('App\Products');
    }

    public function children()
    {
        return $this->hasMany('App\Category', 'prev');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function parent()
    {
        return $this->belongsTo('App\Category', 'prev');
    }

    public function parentRecursive()
    {
        return $this->parent()->with('parentRecursive');
    }

    public function validChildren() {
        return $this->children()
            ->where('flag', '!=', config('agent.flag.deleted'))
            ->get();
    }

    public function hasSubCategory() {
        $anyChild = $this
            ->children()
            ->where('flag', '!=', config('agent.flag.deleted'))
            ->first();

        if ($anyChild) {
            return true;
        }
        else {
            return false;
        }
    }

    public function activeProductsCount() {
        return $this
            ->products()
            ->where('flag', '!=', config('agent.flag.deleted'))
            ->count();
    }

    /**
     * @param $id
     * @param $agent
     * @return array
     * @throws Exception
     */
    public static function getChildCategoriesForParent($parent, $agent) {
        try {
            $categories = null;

            if ($parent === 'null') {

                $categories = Category::getOrFetchDefaultIntents($agent);

                if ($categories && count($categories) > 0) {
                    $root = $categories[0];
                    $root->name = 'Main';
                }

            } else {
                $categories = Category::where([
                    'agent_id' => $agent->id,
                    'prev' => $parent
                ])
                    ->where('flag', '!=', config('agent.flag.deleted'))
                    ->get();
            }

            if ($categories != null) {
                foreach($categories as $category) {
                    $category->has_subcategory = $category->hasSubCategory();
                    $category->product_count = $category->activeProductsCount();
                    $category->synonyms = json_decode($category->synonyms);
                    $category->text_response = json_decode($category->text_response);
                }
            }

            return $categories;

        }catch ( Exception $e ){
            throw $e;
        }
    }

    /**
     * Should be called before loading the page
     * @return array
     */
    public static function getOrFetchDefaultIntents( $active_agent ) {
        if ($active_agent->apiai_dev_access_token === null) {
//            Log::info('Could not fetch default intents because no dev token.');
            return [];
        }

        if ($active_agent->is_default_intents_fetched == false) {
            $response = ApiaiIntentAPI::getIntents($active_agent);
            if ($response !== null) {
                $json_res = $response;

                if (count($json_res) > 1) {
                    $defaultCategories = [];

                    foreach ($json_res as $json) {
                        $category = new Category();

                        $category->agent_id         = $active_agent->id;
                        $category->name             = $json['name'];
                        $category->apiai_intent_id  = $json['id'];
                        $category->prev             = null;

                        if (strcmp($category->name, 'Default Welcome Intent') === 0) {
                            $category->flag = config('agent.flag.default');
                            array_push($defaultCategories, $category);
                        }
                        else {
                            $category->flag = config('agent.flag.uneditable');
                        }

                        $category->save();
                    }

                    $active_agent->is_default_intents_fetched = true;
                    $active_agent->save();

//                    Log::info($defaultCategories);

                    if (count($defaultCategories) > 0) {
                        return $defaultCategories;
                    }
                    else
                        return null;
                }
                else
                    return null;
            }

            return null;
        }
        else {
            $categories = Category::where([
                'agent_id' => $active_agent->id,
                'prev' => null,
                'flag' => config('agent.flag.default')
            ])
                ->get();
            return $categories;
        }
    }

    public static function getDefaultIntent($agent) {
        return Category::where([
            'agent_id' => $agent->id,
            'prev' => null,
            'flag' => config('agent.flag.default')
        ])
            ->first();
    }

    public static function getChainStr($recursiveCats) {

        if ($recursiveCats == null || $recursiveCats->prev == null) return null;

        $chainStr = $recursiveCats->name;
        $nextParent = isset($recursiveCats->parentRecursive) ? $recursiveCats->parentRecursive : null;

        while ($nextParent != null && $nextParent->prev != null) {
            $chainStr = $nextParent->name . ' > ' . $chainStr;
            $nextParent = isset($nextParent->parentRecursive) ? $nextParent->parentRecursive : null;
        };

        return $chainStr;
    }

    public static function allSelectableCategories() {
        $user = Auth::user();
        $agent_code = Redis::get('agent_code_'.$user->id);
        $current_agent = Agents::where('agent_code', $agent_code )->first();

        $absoluteChildrens = DB::select(
            'SELECT id FROM `categories`
              WHERE id NOT IN
                  ( SELECT prev FROM categories where prev is not null
                  and agent_id = ' . $current_agent->id . '
                  and flag != ' . config('agent.flag.deleted') . ')
              and agent_id = ' . $current_agent->id . '
              and rss_feed = ' . 0 . '
              and flag != ' . config('agent.flag.deleted')
        );

        $data = [];

        foreach($absoluteChildrens as $childID) {
            $childCat = Category::find($childID->id);

            if ($childCat->prev == null) continue;

            $recursiveParents = $childCat->parentRecursive()
                ->get();

            if (count($recursiveParents) > 0) {
                $childCat->chainString = Category::getChainStr($recursiveParents[0]);
            }
            else {
                $childCat->chainString = null;
            }

            array_push($data, $childCat);
        }

        return $data;
    }
}
