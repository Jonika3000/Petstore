<?php

use App\Http\Controllers\CategoryController;
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
Route::post('/pets/{petId}/uploadImage', [PetController::class, 'uploadImage']);
Route::post('/pets/{petId}', [PetController::class, 'updateWithForm']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
