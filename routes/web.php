<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('locale/{locale}', function ($locale){
    Session::put('locale', $locale);
    return redirect()->back();
});


Route::get('/', 'HomeController@index')->name('front_page');
Route::get('/privacy', 'HomeController@privacy');
Route::get('/terms', 'HomeController@terms');

Route::get('/m', 'HomeController@demo');
Route::post('/sendmail', 'HomeController@sendmail');
Route::get('/webview/{cart}', 'WebviewController@cart');

Auth::routes();

Route::get('/verify_email/{token}', 'UserController@verify');
Route::post('/resend_verification_mail', 'UserController@resend_verification_mail');


Route::get('/usha_etavirpger', 'Auth\RegisterController@showUshaRegister')->name('register.create');

//Route::get('/register_by_fb/', 'Auth\RegisterController@register_by_facebook')->name('facebook_registration');


Route::get('/demo_page/{agent_code}/', 'DashboardController@demo_page',['middleware'=>['auth','role:admin']])->middleware('agent')->name('agent');
Route::get('/dashboard/{agent_code}/', 'DashboardController@index',['middleware'=>['auth','role:admin']])->middleware('agent')->name('agent');

Route::get('{agent_code}/products/', 'ProductsController@index',['middleware'=>['auth']])->middleware('agent')->name('products');
Route::get('{agent_code}/products/{product_id}', 'ProductsController@update_view',['middleware'=>['auth']])->middleware('agent')->name('product.update');;
Route::post('/product/update', 'ProductsController@update',['middleware'=>['auth']]);
Route::get('{agent_code}/product/create', 'ProductsController@create',['middleware'=>['auth']])->middleware('agent')->name('product.create');
Route::get('{agent_code}/categories/', 'CategoryChainController@index',['middleware'=>['auth']])->middleware('agent')->name('categories');
Route::get('{agent_code}/orders/', 'OrdersController@index',['middleware'=>['auth']])->middleware('agent')->name('orders');
Route::get('/bots', 'DashboardController@home',['middleware'=>['auth']])->name('bots');
Route::get('/profile', 'ProfileController@profile',['middleware'=>['auth']])->name('profile');
Route::post('/profile/store', 'ProfileController@store',['middleware'=>['auth']]);
Route::get('{agent_code}/change_plan', 'ChangePlanController@change_plan',['middleware'=>['auth']])->middleware('agent')->name('change_plan');
Route::get('{agent_code}/configure', 'ConfigureController@configure',['middleware'=>['auth']])->middleware('agent')->name('settings');
Route::get('{agent_code}/schedules', 'SchedulesController@index',['middleware'=>['auth']])->middleware('agent')->name('schedule');
Route::get('{agent_code}/chat_inbox', 'ChatInboxController@chat_inbox',['middleware'=>['auth']])->middleware('agent')->name('chat_inbox');

/*
 * Manage Url Lists
 */
// Route::get('/{agent_code}/feedback', 'DashboardController@feedback',['middleware'=>['auth','role:admin']])->middleware('agent')->name('feedback');
// Route::get('/{agent_code}/faq', 'DashboardController@faq',['middleware'=>['auth','role:admin']])->middleware('agent')->name('faq');


/*
 * Artificial Intelligent routes
 */
Route::get('/artificial_intelligent/{agent_code}/', 'DashboardController@artificialIntelligent',['middleware'=>['auth','role:admin']])->middleware('agent')->name('AI');



Route::post('/get_current_order', 'OrdersController@current_order',['middleware'=>['auth']]);
Route::post('/update_order', 'OrdersController@order_update',['middleware'=>['auth']]);

Route::post('/agent_lists', 'AgentController@agent_lists',['middleware'=>['auth']]);

Route::group(['prefix' => '{agent_code}/upload', 'middleware' => ['auth','role:admin|ulkabot']], function() {

    Route::get('/image','UploadProcessController@imageUpload')->middleware('agent')->name('image_upload');
    Route::get('/csv','UploadProcessController@csvUpload')->middleware('agent')->name('csv_upload');

});

Route::post('/upload/image_upload_process','UploadProcessController@storeImage',['middleware' => ['auth']]);

Route::post('/upload/csv_upload_process','UploadProcessController@csv_upload_process',['middleware' => ['auth','role:admin']]);

Route::post('/upload/csv_upload_process','UploadProcessController@csv_upload_process')->middleware('auth');

Route::post('product/store', 'ProductsController@product_store')->middleware('auth');

/*
 * Facebook Login routes
 */

Route::get('{agent_code}/connect_fb_page', 'FacebookPlatformConnection@index',['middleware'=>['auth']])->middleware('agent')->name('connect_fb');
Route::get('{agent_code}/connect_web_page', 'WebPlatformConnection@index',['middleware'=>['auth']])->middleware('agent')->name('connect_web');
Route::post('/facebook_login_callback', 'FacebookPlatformConnection@facebook_login_callback',['middleware'=>['auth']]);
// Route::get('/connect_facebook', 'FacebookPlatformConnection@connect_fb',['middleware'=>['auth']])->name('connect_fb');

/*
 * Webhooks
 * Facebook
 * */

//Route::get('/{agent_code}/webhook','FacebookMessengerController@get_webhook');
//Route::post('/{agent_code}/webhook','FacebookMessengerController@post_webhook');

/*
 * Webhook
 * API.AI
 * */

Route::post('/apiaiwebhook','ApiaiWebhookRequestController@apiai_webhook');
