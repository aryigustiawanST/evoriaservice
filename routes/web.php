<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('bank', ['uses' => 'TopMenuController@bank']);

$router->group(['prefix' => 'auth'], function() use ($router){
    $router->post('user/login', ['uses' => 'AuthController@login']);
    $router->post('user/register', ['uses' => 'AuthController@register']);
    $router->post('user/forgot-password', ['uses' => 'AuthController@forgotpassword']);

    $router->post('vendor/login', ['uses' => 'AuthController@loginvendor']);
    $router->post('vendor/register', ['uses' => 'AuthController@registervendor']);
    $router->post('vendor/forgot-password', ['uses' => 'AuthController@forgotpasswordvendor']);
});

$router->group(['prefix' => 'product'], function() use ($router){
    $router->get('category/list', ['uses' => 'ProductController@showcategory']);
    $router->post('add', ['uses' => 'ProductController@add']);
    $router->post('edit/{id}', ['uses' => 'ProductController@edit']);
    $router->post('delete/{id}', ['uses' => 'ProductController@delete']);
    $router->get('{id}', ['uses' => 'ProductController@productid']);
});

$router->group(['prefix' => 'productcategory'], function() use ($router) {
    $router->post('add', ['uses' => 'ProductController@addcategory']);
    $router->post('edit/{id}', ['uses' => 'ProductController@editcategory']);
    $router->post('delete/{id}', ['uses' => 'ProductController@deletecategory']);
});

$router->group(['prefix' => 'client'], function() use ($router){
    $router->post('profile', ['uses' => 'ClientController@profile']);
    $router->post('saveprofile', ['uses' => 'ClientController@saveprofile']);   
    $router->post('profile/saveaddress', ['uses' => 'ClientController@saveaddress']);
    $router->post('profile/saverekening', ['uses' => 'ClientController@saverekening']);

    $router->post('feed/addlike', ['uses' => 'FeedController@addlike']);
    $router->post('feed/removelike', ['uses' => 'FeedController@removelike']);

    $router->post('feed/addcomment', ['uses' => 'FeedController@addcomment']);
    // $router->post('feed/removelike', ['uses' => 'FeedController@removelike']);
});
$router->group(['prefix' => 'page'], function() use ($router){
    $router->get('home/top_menu', ['uses' => 'TopMenuController@index']);
    $router->post('home/top_menu/add', ['uses' => 'TopMenuController@add']);    
    $router->post('home/top_menu/edit/{id}', ['uses' => 'TopMenuController@edit']);
    $router->post('home/top_menu/delete/{id}', ['uses' => 'TopMenuController@delete']);
    
    $router->get('home/feed', ['uses' => 'FeedController@index']);
    $router->get('home/feed/vendor/{id}', ['uses' => 'FeedController@show']);
    $router->post('home/feed/add', ['uses' => 'FeedController@add']);
    $router->post('home/feed/edit/{id}', ['uses' => 'FeedController@edit']);
    $router->post('home/feed/delete/{id}', ['uses' => 'FeedController@delete']);
});

$router->group(['prefix' => 'vendor'], function() use ($router){
   // $router->post('profile', ['uses' => 'VendorController@profile']);
    $router->post('saveprofile', ['uses' => 'VendorController@saveprofile']);
    $router->post('profile/updatesaldo', ['uses' => 'VendorController@updatesaldo']);
    $router->post('profile/updaterating', ['uses' => 'VendorController@updaterating']);
    $router->post('profile/updatetoko', ['uses' => 'VendorController@updatetoko']);
    $router->post('profile/saverekening', ['uses' => 'VendorController@saverekening']);

    $router->get('profile/{vendorid}', ['uses' => 'VendorController@profile']);

    $router->get('rekening/{vendorid}', ['uses' => 'VendorController@rekening']);

    $router->get('product/show/{id}', ['uses' => 'ProductController@show']);
    $router->post('product/addproduct', ['uses' => 'ProductController@add']);
    $router->post('product/stock/update', ['uses' => 'ProductController@updatestock']);
    $router->get('product/variasi/list', ['uses' => 'ProductController@variasilist']);
    $router->post('product/variasi/nilai', ['uses' => 'ProductController@variasinilai']);
    $router->post('product/variasi/addnilai', ['uses' => 'ProductController@addvariasinilai']);
    
    $router->post('product/variant/update', ['uses' => 'ProductController@updatevariant']);
    
    $router->post('product/photo/update', ['uses' => 'ProductController@updatephoto']);

    $router->post('edit/{id}', ['uses' => 'ProductController@edit']);
    $router->post('delete/{id}', ['uses' => 'ProductController@delete']);

    $router->get('feed/showbyfeedid/{id}', ['uses' => 'FeedController@showbyfeedid']);

});