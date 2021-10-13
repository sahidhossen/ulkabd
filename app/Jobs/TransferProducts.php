<?php

namespace App\Jobs;

use App\Agents;
use App\Category;
use App\Products;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Illuminate\Support\Facades\Log;

class TransferProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $agent;
    protected $products;
    protected $transferCategoryId;

    /**
     * TransferProducts constructor.
     * @param $products
     * @param $transferCategoryId
     */
    public function __construct($products, $transferCategoryId)
    {
        $this->products = $products;
        $this->transferCategoryId = $transferCategoryId;

        $this->agent = Agents::getCurrentAgent();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $flag = config('agent.flag.updated');

            $category = Category::find($this->transferCategoryId);

            $children = $category->validChildren();

            if ((isset($children) && count($children) > 0) ||
                $category->flag === config('agent.flag.default')) {
                $flag = config('agent.flag.uncategorized');
            }

            foreach($this->products as $productData) {
                $product = Products::find($productData['id']);

                if ($product != null) {
                    $product->flag = $flag;
                    $product->category_id = $this->transferCategoryId;

                    $product->save();
                }
            }
        }
        catch( Exception $e ) {
            Log::info('TransferProducts Exception: ' . $e->getMessage());
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('TransferProducts Job failed due to ' . $exception->getMessage());
    }
}
