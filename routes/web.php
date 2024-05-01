<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/link', function () {
    Artisan::call('storage:link');
});



// Route::get('/token', function () {
//     Artisan::call('schedule:work');
//     // Artisan::call('token:expire');
// });
// Artisan::call('schedule:run');


Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('route:cache');
    Artisan::call('view:clear');
});
