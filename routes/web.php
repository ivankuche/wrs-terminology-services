<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FHIRValueSetController;
use App\Http\Controllers\SnomedCTController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('FHIRValueSet/methods',[FHIRValueSetController::class, 'getMethods']);
Route::get('FHIRValueSet/{ValueSet}/{Term?}/{Sort?}',[FHIRValueSetController::class, 'getValueSet']);
Route::get('SnomedCT/{ConceptGroup}/{Term?}/{Sort?}',[SnomedCTController::class, 'query']);
Route::get('ValueSet/{ValueSet}/{Term?}/{Sort?}',[ValueSetController::class, 'getValueSet']);
