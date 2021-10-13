<?php

namespace App\Jobs;

use App\Agents;
use App\ApiaiEntity;
use App\ApiaiEntityAPI;
use App\ApiaiIntent;
use App\ApiaiIntentAPI;
use App\Category;
use App\CategoryToIntentMapper;
use App\Events\BroadcastTrainingStatus;
use App\FacebookResponseTypes;
use App\Products;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Exception;

class APIAIAgentUpdater implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, DispatchesJobs;

    private $agent, $user;

    private $bAgentTrainingCompletedSuccessfully;

    /**
     * APIAIAgentUpdater constructor.
     * @param $agent
     */
    public function __construct($agent, $user)
    {
        $this->agent = $agent;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->bAgentTrainingCompletedSuccessfully = true;

        if ($this->agent->training_status == config('agent.training.needed')) {

            $this->agent->training_status = config('agent.training.running');
            $this->agent->save();

            Log::info("Training Started...");
            event(
                new BroadcastTrainingStatus(
                    [
                        'status' => config('agent.training.running')
                    ],
                    $this->user
                )
            );

            $defaultCategory = Category::getDefaultIntent($this->agent);
            $this->updateApiaiFor($defaultCategory);

            if ($this->bAgentTrainingCompletedSuccessfully == true) {
                $training_status = config('agent.training.done');
                Log::info("Training Finished Succesfully.");
            }
            else {
                $training_status = config('agent.training.needed');
                Log::info("Training Finished UnSuccessfully...!");
            }

            $this->agent->training_status = $training_status;
            $this->agent->save();

            event(
                new BroadcastTrainingStatus(
                    [
                        'status' => $training_status
                    ],
                    $this->user
                )
            );
        }
        else {
            Log::info('Agnet training not required.');
        }
    }

    private function categoryDetail($category) {
        try {
            $filtered = new class{};
            $filtered->deletedSubs = [];
            $filtered->usableSubs = [];
            $filtered->countProduct = 0;
            $filtered->hasChange = (
                $category->flag !== config('agent.flag.ok')
                &&
                $category->flag !== config('agent.flag.default')
            ) ? true : false;
            $filtered->isAbs = ($category->flag === config('agent.flag.default')) ? false : true;
            $filtered->countModifiedProducts = 0;

            if ($category) {
                $subCategories = $category
                    ->children()
                    ->get();

                foreach($subCategories as $subCategory) {

                    switch($subCategory->flag) {
                        case config('agent.flag.ok'):
                            $filtered->isAbs = false;
                            array_push($filtered->usableSubs, $subCategory);
                            break;
                        case config('agent.flag.created'):
                            $filtered->isAbs = false;
                            $filtered->hasChange = true;
                            array_push($filtered->usableSubs, $subCategory);
                            break;
                        case config('agent.flag.updated'):
                            $filtered->isAbs = false;
                            $filtered->hasChange = true;
                            array_push($filtered->usableSubs, $subCategory);
                            break;
                        case config('agent.flag.deleted'):
                            $filtered->hasChange = true;
                            array_push($filtered->deletedSubs, $subCategory);
                            break;
                    }
                }

                if ($filtered->isAbs === true) {
                    $filtered->countProduct = $category
                        ->products()
                        ->where('flag', '!=', config('agent.flag.deleted'))
                        ->where('flag', '!=', config('agent.flag.uncategorized'))
                        ->count();

                    $filtered->countModifiedProducts = $category
                        ->products()
                        ->where('flag', '!=', config('agent.flag.ok'))
                        ->where('flag', '!=', config('agent.flag.uncategorized'))
                        ->count();

                    if ($filtered->countModifiedProducts > 0) {
                        $filtered->hasChange = true;
                    }
                }

                return $filtered;
            }
            else
                return $filtered;
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function getOneUsableProduct($category) {
        try {
            if ($category) {
                $pData = $category
                    ->products()
                    ->where('flag', '=', config('agent.flag.ok'))
                    ->first(['code']);

                return ($pData != null) ? $pData->code : null;
            }

            else return null;
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function deleteEntity($category) {
        try {
            if ($category->apiai_entity_id != null) {
                $res = ApiaiEntityAPI::deleteEntityWithId($category->apiai_entity_id, $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not delete Entity: ' . $category->apiai_entity_name
                        . ' because: ' . $res['message']);
                }

                $category->apiai_entity_id = null;
                $category->apiai_entity_name = null;
                $category->save();
            }
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function deleteIntent($category) {
        try {
            if ($category->apiai_intent_id != null) {
                $res = ApiaiIntentAPI::deleteIntentWithId($category->apiai_intent_id, $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not delete Intent: ' . $category->apiai_intent_name
                        . ' because: ' . $res['message']);
                }

                $category->apiai_intent_id = null;
                $category->apiai_intent_name = null;
                $category->save();
            }
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function fetchAndUpdateEntityData($category) {
        try {
            if ($category->apiai_entity_id != null) {
                $entityData = ApiaiEntityAPI::getEntityWithId($category->apiai_entity_id, $this->agent);

                if (isset($entityData['error'])) {
                    throw new Exception('Could not get Entity: ' . $category->apiai_intent_name
                        . ' because: ' . $entityData['message']);
                }

                $eDataObject = new ApiaiEntity(null, $entityData);
                $eDataObject->setName($category->name);
            }
            else {
                $eDataObject = new ApiaiEntity($category);
            }

            //Log::info(print_r($eDataObject->getData(), true));

            return $eDataObject;
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function fetchAndUpdateIntentData($category, $filteredCats, $flag) {
        try {
            if ($category->apiai_intent_id != null) {
                $intentData = ApiaiIntentAPI::getIntentWithId($category->apiai_intent_id, $this->agent);

                if (isset($intentData['error'])) {
                    throw new Exception('Could not get Intent: ' . $category->apiai_intent_name
                        . ' because: ' . $intentData['message']);
                }

                $dataObject = new ApiaiIntent(null, $intentData);
            }
            else {
                $dataObject = new APIAIIntent($category);
            }

            if ($flag !== config('agent.flag.default')) {
                // Set Intent name
                $dataObject->setName($category->name);

                // Set action slots
                $dataObject->setActionSlots(
                    ($filteredCats->isAbs === true && $filteredCats->countProduct > 0) ?
                        CategoryToIntentMapper::actionSlots($category) : []
                );

                // Set webhook status
                $dataObject->setWebhookStatus(
                    ($filteredCats->isAbs === true &&
                        ($filteredCats->countProduct > 0 || $category->rss_feed == true)
                    ) ? true : false
                );

                // Prepare Template data and userSays product code
                $oneProductCode = ($filteredCats->isAbs === true && $filteredCats->countProduct > 0) ?
                    $this->getOneUsableProduct($category) : null;
                $templates = [$category->name];
                if ($oneProductCode !== null) {
                    $templates[] = '@' . $category->apiai_entity_name . ':' . $category->apiai_entity_name;
                }

                // Set Templates
                $dataObject->setTemplates(
                    $templates
                );

                // Set user says
                $userSays = $category->synonyms ? array_values(json_decode($category->synonyms)) : null;

                $dataObject->setUserSays(
                    CategoryToIntentMapper::userSaysDataArray(
                        $category,
                        $userSays,
                        $oneProductCode
                    )
                );

                // Set Text response
                if ($oneProductCode == null) {
                    $dataObject->setTextResponse(
                        $category->text_response ? array_values(json_decode($category->text_response)) : null
                   );
                }
            }

            // Set FB response
            $dataObject->deleteFBResponseIfAvailable();

            $cardsData = CategoryToIntentMapper::cardsDataArray($filteredCats->usableSubs);

            if ($cardsData) {
                $dataObject->setFBResponse(
                    FacebookResponseTypes::genericCardsWith($cardsData)
                );
            }
            else {
                $quickRepliesData = CategoryToIntentMapper::quickRepliesDataArray(
                    $filteredCats->usableSubs
                );

                $dataObject->setFBResponse(
                    FacebookResponseTypes::quickRepliesWith(
                        'You can choose:',
                        $quickRepliesData
                    )
                );
            }

            // Log::info(print_r($dataObject->getData(), true));

            return $dataObject;
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function updateApiaiEntity($category, $eDataObject) {
        try {
            if ($category->apiai_entity_id != null) {
                $res = ApiaiEntityAPI::putEntity($category, $eDataObject->getData(), $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not put Entity: ' . $category->apiai_intent_name
                        . ' because: ' . $res['message']);
                }

                $category->apiai_entity_name = $eDataObject->getName();
                $category->save();
            }
            else {
                $res = ApiaiEntityAPI::postEntity($eDataObject->getData(), $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not post Entity: ' . $category->apiai_intent_name
                        . ' because: ' . $res['message']);
                }

                $category->apiai_entity_id = $res['id'];
                $category->apiai_entity_name = $eDataObject->getName();
                $category->save();
            }
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function updateApiaiIntent($category, $dataObject, $flag) {
        try {
            if ($category->apiai_intent_id != null) {
                $res = ApiaiIntentAPI::putIntent($category, $dataObject->getData(), $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not Put Intent: ' . $category->apiai_intent_name
                        . ' because: ' . $res['message']);
                }

                if ($flag !== config('agent.flag.default')) {
                    $category->apiai_intent_name = $category->name;
                    $category->flag = config('agent.flag.ok');
                    $category->save();
                }
            }
            else {
                $res = ApiaiIntentAPI::postIntent($dataObject->getData(), $this->agent);

                //Log::info($res);

                if (isset($res['error'])) {
                    throw new Exception('Could not Post Intent: ' . $category->apiai_intent_name
                        . ' because: ' . $res['message']);
                }

                $category->apiai_intent_id = $res['id'];
                $category->apiai_intent_name = $category->name;
                $category->flag = config('agent.flag.ok');
                $category->save();
            }
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function synchronizeProductEntries($category) {
        try {
            // read non OK products under category in chunk

            $category->products()
                ->where('flag', '!=', config('agent.flag.ok'))
                ->where('flag', '!=', config('agent.flag.uncategorized'))
                ->chunk(
                    50,
                    function ($products) use ($category) {

                        $pToDelete = [];
                        $pToUpdate = [];
                        $pToCreate = [];

                        foreach($products as $product) {
                            //Log::info('Product: ' . $product->name);

                            $pFlag = $product->flag;

                            $pName = preg_replace('/[^\w\s]+/u','' ,$product->name);
                            Log::info("PName: " . $pName);

                            switch($pFlag) {
                                case config('agent.flag.created'):
                                    $eCData = ApiaiEntity::entryWith(
                                        $product->id,
                                        [
                                            $product->code,
                                            $pName,
                                            $category->apiai_entity_name . '-' .  $product->code
                                        ]
                                    );
                                    array_push($pToCreate, $eCData);
                                    break;

                                case config('agent.flag.updated'):
                                    $eUData = ApiaiEntity::entryWith(
                                        $product->id,
                                        [
                                            $product->code,
                                            $pName,
                                            $category->apiai_entity_name . '-' .  $product->code
                                        ]
                                    );
                                    array_push($pToUpdate, $eUData);
                                    break;

                                case config('agent.flag.deleted'):
                                    array_push($pToDelete, $product->id);
                                    break;
                            }

                        }

                        // delete
                        if (count($pToDelete) > 0) {
                            if ($category->apiai_entity_id != null) {
                                $eDRes = ApiaiEntityAPI::deleteEntriesFromEntityWithId(
                                    $category->apiai_entity_id,
                                    $pToDelete,
                                    $this->agent
                                );
                                if (isset($eDRes['error'])) {
                                    throw new Exception('Could not delete Entity Entries because: ' . $eDRes['message']);
                                }
                            }

                            usleep(10000); // 0.01 sec
                        }

                        // update
                        if (count($pToUpdate) > 0 && $category->apiai_entity_id != null) {
                            $eURes = ApiaiEntityAPI::updateEntriesToEntityWithId(
                                $category->apiai_entity_id,
                                $pToUpdate,
                                $this->agent
                            );
                            if (isset($eURes['error'])) {
                                throw new Exception('Could not update Entity Entries because: ' . $eURes['message']);
                            }

                            usleep(10000); // 0.01 sec
                        }

                        // create
                        if (count($pToCreate) > 0 && $category->apiai_entity_id != null) {
                            $eCRes = ApiaiEntityAPI::addEntriesToEntityWithId(
                                $category->apiai_entity_id,
                                $pToCreate,
                                $this->agent
                            );
                            if (isset($eCRes['error'])) {
                                throw new Exception('Could not add Entity Entries because: ' . $eCRes['message']);
                            }

                            usleep(10000); // 0.01 sec
                        }
                    }
                );

            $category->products()
                ->where('flag', '=', config('agent.flag.deleted'))
                ->delete();

            $category->products()
                ->where('flag', '=', config('agent.flag.created'))
                ->orWhere('flag', '=', config('agent.flag.updated'))
                ->update(['flag' => config('agent.flag.ok')]);
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    private function updateApiaiFor($category) {
        try {
            if ($category) {
                $categoryDetail = $this->categoryDetail($category);
//                //Log::info(print_r($categoryDetail, true));

                $flag = $category->flag;

                //Log::info('Updating category: ' . $category->name);

                switch ($flag) {
                    case config('agent.flag.deleted'): {

                        if ( $categoryDetail->hasChange === true ) {
                            // Delete Intent
                            $this->deleteIntent($category);
                            usleep(10000); // 0.01 sec

                            // Delete Entity
                            $this->deleteEntity($category);
                            usleep(10000); // 0.01 sec

                            // Delete Category
                            $category->delete();
                        }
                        else {
                            //Log::info('No category|intent update! in ' . $category->name . '.');
                        }

                    }
                        break;

                    default: {

                        if ($flag !== config('agent.flag.deleted')
                            &&
                            $categoryDetail->isAbs === true && $categoryDetail->countProduct > 0 ) {

                            //Log::info('Creating...');

                            // Entity
                            if($category->apiai_entity_id == null || $categoryDetail->hasChange === true) {

                                // Delete Intent
                                if ($categoryDetail->hasChange === true &&
                                    $category->apiai_intent_id != null &&
                                    $category->name !== $category->apiai_intent_name) {

                                    Log::info('Deleting category because intent name is not in match');
                                    $this->deleteIntent($category);
                                    usleep(10000); // 0.01 sec -> larger wait time!

                                }

                                $eDataObject = $this->fetchAndUpdateEntityData($category);
                                usleep(10000); // 0.01 sec
                                $this->updateApiaiEntity($category, $eDataObject);
                                usleep(10000); // 0.01 sec

                            }

                            // Update Entries|Products
                            if($category->apiai_entity_id != null && $categoryDetail->countModifiedProducts > 0) {
                                $this->synchronizeProductEntries($category);
                            }
                            else {
                                //Log::info('No Entries update for ' . $category->name);
                            }

                            if ( $categoryDetail->hasChange === true ) {
                                // Intent
                                $dataObject = $this->fetchAndUpdateIntentData($category, $categoryDetail, $flag);
                                usleep(10000); // 0.01 sec
                                $this->updateApiaiIntent($category, $dataObject, $flag);
                                usleep(10000); // 0.01 sec
                            }
                            else {
                                //Log::info('No category|intent update! in ' . $category->name . '.');
                            }

                        }
                        else {

                            //Log::info('Deleting...');

                            if ( $categoryDetail->hasChange === true ) {
                                // Intent
                                $dataObject = $this->fetchAndUpdateIntentData($category, $categoryDetail, $flag);
                                usleep(10000); // 0.01 sec
                                $this->updateApiaiIntent($category, $dataObject, $flag);
                                usleep(10000); // 0.01 sec
                            }
                            else {
                                //Log::info('No category|intent update! in ' . $category->name . '.');
                            }

                            // Delete Entries|Products
                            if ($categoryDetail->countModifiedProducts > 0) {
                                $this->synchronizeProductEntries($category);
                            }

                            // Delete Entity
                            $this->deleteEntity($category);
                            usleep(10000); // 0.01 sec
                        }

                    }
                        break;
                }

                $allSubs = array_merge($categoryDetail->deletedSubs, $categoryDetail->usableSubs);
                foreach($allSubs as $subCategory) {
                    usleep(10000); // 0.01 sec
                    $this->updateApiaiFor($subCategory);
                }
            }
        }
        catch( Exception $e ) {
            Log::info('Training failed due to ' . $e->getMessage());
            $this->bAgentTrainingCompletedSuccessfully = false;
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('Job failed due to ' . $exception->getMessage());

        if ($this->agent->training_status == config('agent.training.running')) {
            $this->agent->training_status = config('agent.training.needed');
            $this->agent->save();

            event(
                new BroadcastTrainingStatus(
                    ['status' => config('agent.training.needed')],
                    $this->user
                )
            );
        }
    }
}
