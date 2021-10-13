<?php

namespace App;

use App\Events\BroadcastProductUploaded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class Products extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agent_id', 'category_id', 'name', 'code', 'product_attributes', 'price',
        'offer_price', 'priority', 'detail', 'is_image', 'unit', 'flag',
        'created_at', 'updated_at', 'stock', 'external_link', 'social_posts'
    ];

    /*
     * Get a agents ID
     */
    public function agent()
    {
        return $this->belongsTo('App\Agents');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public static function processNewCSVRowData($user, $csv_row, $agent_id, $category_id) {

        try {
            $csv_json = json_decode($csv_row, true);

            $product = Products::where([
                'code' => $csv_row->code,
                'agent_id' => $agent_id
            ])
                ->first();

            $p_new = false;

            if ($product == null) {
                $p_new = true;
                $product = new Products();
                $product->agent_id = $agent_id;
                $product->code = $csv_row->code;

                $product->flag = config('agent.flag.created');
            }
            else if (
                $product->flag !== config('agent.flag.created')
                &&
                ( $product->name != $csv_row->name || $product->code != $csv_row->code )
            ) {
                $product->flag = config('agent.flag.updated');
            }
            else if ($product->flag === config('agent.flag.deleted')) {
                $p_new = true;
                $product->flag = config('agent.flag.updated');
            }

            $product->name = $csv_row->name;
            $product->category_id = $category_id;
            $product->price = $csv_row->price;
            $product->offer_price = $csv_row->offer_price;
            $product->priority = $csv_row->priority;
            $product->detail = $csv_row->detail;
            $product->is_image = null;
            $product->unit = $csv_row->unit ? $csv_row->unit : 'unit';
            $product->stock = $csv_row->stock;
            $product->image_link = $csv_row->image_link;
            $product->external_link = $csv_row->external_link;

            $category = Category::find($category_id);
            $attributeFields = $category->required_attributes ? explode(',', $category->required_attributes) : null;
            if ($attributeFields) {
                $product_attributes = [];

                foreach($attributeFields as $attributeField) {
                    $product_attributes[$attributeField] = $csv_json[$attributeField];
                }

                $product->product_attributes = ($product_attributes !== null && count($product_attributes) > 0) ?
                        json_encode($product_attributes) : null;
            }

            $product->save();

            if ($p_new == true) {
                event(
                    new BroadcastProductUploaded(
                        $product,
                        $user
                    )
                );
            }

            return true;
        }
        catch (Exception $e) {
            Log::info('Products Exception: ' . $e->getMessage());
        }
    }

    /*
     * Get all product lists based on agents
     */

    public function product_list( $agent_id ){

        $product_list = Products::where(array('agent_id'=>$agent_id))->get();

        if( $product_list )
            return $product_list;

        return null;
    }

    /*
     * return number of result
     * @return integer
     */
    public static function agentTotalProduct( $agent_id ){

        $product_counter = Products::where('agent_id', $agent_id)->count();
        return $product_counter;
    }

    public static function totalUsableProducts($agent = null) {
        try {
            if ($agent === null)
                $agent = Agents::getCurrentAgent();

            if ($agent == null) throw new Exception();

            $countProducts = $agent->products()
                ->where('flag', '!=', config('agent.flag.uncategorized'))
                ->where('flag', '!=', config('agent.flag.deleted'))
                ->count();

            return $countProducts;

        }catch(Exception $e ) {
            return 0;
        }
    }

    public static function updateFlag($from, $to, Category $category) {
        try {
            $category->products()
                ->where('flag', '=', $from)
                ->update(['flag' => $to]);

            return true;

        } catch(Exception $e) {
            return false;
        }
    }
}
