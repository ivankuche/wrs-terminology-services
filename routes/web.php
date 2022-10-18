<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FHIRValueSetController;
use App\Http\Controllers\SnomedCTController;
use App\Http\Controllers\TerminologyServiceController;

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
Route::get('FHIRValueSet/{ValueSet}/{Term?}/{Sort?}',[FHIRValueSetController::class, 'getResults']);
Route::get('SnomedCT/methods',[SnomedCTController::class, 'getMethods']);
Route::get('SnomedCT/{ConceptGroup}/{Term?}/{Sort?}',[SnomedCTController::class, 'getResults']);

Route::get('TerminologyService/{ValueSet}/methods',[TerminologyServiceController::class, 'getMethods']);
Route::get('TerminologyService/{ValueSet}/{Term?}/{Sort?}',[TerminologyServiceController::class, 'getValueSet']);
