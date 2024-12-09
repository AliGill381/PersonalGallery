<?php

use App\Http\Controllers\FaceRecognitionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/face/compare' , [FaceRecognitionController::class , 'compareFaces'])->name('face.regonization');
