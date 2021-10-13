<?php

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//
//Route::middleware('auth:api')->post('/users', function (Request $request) {
//    return Auth::user();
//});
//
//Route::middleware('auth:api')->get('/all_users', function (Request $request) {
//    return User::all();
//});

Route::middleware('auth:api')->post('/resend_verification_mail', 'UserController@resend_verification_mail');

Route::middleware('auth:api')->post('/dashboard_initial_information', 'DashboardController@get_dashboard_initial_information');


Route::middleware('auth:api')->post('/agent_lists', 'AgentController@agent_lists');
Route::middleware('auth:api')->post('/add_agent', 'AgentController@add_agent');
Route::middleware('auth:api')->post('/update_agent', 'AgentController@update_agent');
Route::middleware('auth:api')->get('/agent', 'AgentController@get_agent');

/*
 * All Products calls
 */
Route::middleware('auth:api')->post('/all_products', 'ProductsController@api_all_product');
Route::middleware('auth:api')->post('/delete_product', 'ProductsController@delete_product');
Route::middleware('auth:api')->post('/product/{id}', 'ProductsController@patchProduct');


/*
 * Category related
 */
Route::middleware('auth:api')->post('/category_list_by_parent', 'CategoryChainController@getCategoryListByParent');
Route::middleware('auth:api')->post('/default_intents', 'CategoryChainController@checkOrFetchDefaultIntents');
Route::middleware('auth:api')->post('/add_category', 'CategoryChainController@save_category');
Route::middleware('auth:api')->post('/before_delete_operation', 'CategoryChainController@beforeDeleteOperation');
Route::middleware('auth:api')->post('/transfer_and_delete', 'CategoryChainController@transferAndDelete');
Route::middleware('auth:api')->post('/products_by_category', 'ProductsController@productsUnderCategory');
Route::middleware('auth:api')->post('/all_selectable_category', 'CategoryChainController@getAllCategory');
Route::middleware('auth:api')->post('/uncategorized_products', 'ProductsController@uncategorizedProducts');


/*
 * Ajax request for product page
 */
Route::middleware('auth:api')->post('/get_category_and_product_attributes', 'ProductsController@getCategoryAndProductAttributes');
/*
 * Ajax request for training your bot
 */
Route::middleware('auth:api')->post('/train_agent', 'TrainAgentController@train');
Route::middleware('auth:api')->post('/check_training_status', 'TrainAgentController@checkTrainingStatus');
Route::middleware('auth:api')->post('/test_broadcast', 'TrainAgentController@TestBroadcast');

/*
 * CSV upload
 */
Route::middleware('auth:api')->post('/csv_upload', 'UploadProcessController@csv_upload_process');
Route::middleware('auth:api')->post('/dynamic_csv', 'ProductsController@dynamic_csv');

/*
 * Switch agent engines
 */
Route::middleware('auth:api')->post('/agent_engine_switcher', 'AgentController@agent_engine_switcher');
Route::middleware('auth:api')->post('/connect_facebook_page', 'FacebookPlatformConnection@connect_facebook_page');
Route::middleware('auth:api')->post('/disconnect_facebook_page', 'FacebookPlatformConnection@disconnect_facebook_page');


/*
 * Order url
 */
Route::middleware('auth:api')->post('/get_order_by', 'OrdersController@get_order_by');
Route::middleware('auth:api')->post('/change_order_action', 'OrdersController@order_action');

/*
 * Schedule broadcast routes
 */
Route::middleware('auth:api')->post('/schedule/create','SchedulesController@create_schedule');
Route::middleware('auth:api')->get('/schedule/fetch','SchedulesController@broadcast_list_by_agent');

Route::middleware('auth:api')->post('/broadcast/products','BroadcastController@broadcastProducts');

/*
 * Facebook Feed routes
 */
Route::middleware('auth:api')->post('/facebook_post/products','ProductsController@postProductsOnFacebook');

/*
 * Bot web extensions
 * Usha
 * */
Route::post('/bot_web_ext/profile_cart','ProfileCartController@profile_cart');
Route::post('/bot_web_ext/cart_update_checkout','ProfileCartController@cart_update_checkout');
Route::post('/bot_web_ext/user_profile','ProfileCartController@user_profile');
