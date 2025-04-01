<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationTestController;
use App\Http\Controllers\CollectionsTestController;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('communication',CommunicationTestController::class);
Route::resource('collection',CollectionsTestController::class);
