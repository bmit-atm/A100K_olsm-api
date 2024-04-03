<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/generate', 'App\Http\Controllers\SignatureController@generate');

Route::get('/logs', 'App\Http\Controllers\LogController@index');

// Route to delete a log
Route::delete('/logs/{id}', 'App\Http\Controllers\LogController@destroy');



Route::get('/all-groups', 'App\Http\Controllers\Auth\AuthenticatedSessionController@getAllGroups');