<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailVerificationController;
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

$router->post('/verifyemail', 'EmailVerificationController@verifyEmail');
$router->post('/verifymail', 'EmailVerificationController@verifyMail');


// $router->group(['prefix' => 'api'], function () use ($router) {
//     $router->post('verify-email', [EmailVerificationController::class, 'verifyEmail']);
// });

// $router->group(['prefix' => 'api'], function () use ($router) {
//     $router->post('/verifyemail', 'EmailVerificationController@verifyEmail');
// });

// Route::group(['prefix' => 'api'], function () {
//     Route::post('verifyemail', [EmailVerificationController::class, 'verifyEmail']);
// });
