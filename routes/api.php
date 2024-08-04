<?php

use App\Http\Controllers\PetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/pets', [PetController::class, 'index']);
Route::post('/pets', [PetController::class, 'store']);
Route::get('/pets/{id}', [PetController::class, 'show']);
Route::put('/pets/{id}', [PetController::class, 'update']);
Route::delete('/pets/{id}', [PetController::class, 'destroy']);
Route::get('/pets/findByStatus', [PetController::class, 'findByStatus']);
Route::get('/pets/findByTags', [PetController::class, 'findByTags']);
Route::post('/pets/{petId}/uploadImage', [PetController::class, 'uploadImage']);
Route::post('/pets/{petId}', [PetController::class, 'updateWithForm']);
