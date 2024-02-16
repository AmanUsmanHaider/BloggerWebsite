<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\CreateUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/create', [CreateUsers::class, 'create']);


/****************************** Post crud *********************************/
Route::get('/posts', [PostsController::class, 'index']);
Route::post('/posts/create', [PostsController::class, 'create']);
Route::delete('/posts/delete/{id}', [PostsController::class, 'delete']);
Route::delete('/posts/permanentlydelete/{id}', [PostsController::class, 'permanentlydelete']);
Route::post('/posts/filter', [PostsController::class, 'filter']);
Route::post('/posts/update', [PostsController::class, 'update']);
/*************************************************************************/


/****************************** Category  Crud*********************************/
Route::get('/categories', [CategoriesController::class, 'index']);
Route::post('/categories/create', [CategoriesController::class, 'create']);
Route::delete('/categories/delete/{id}', [CategoriesController::class, 'delete']);
Route::delete('/categories/permanentlydelete/{id}', [CategoriesController::class, 'permanentlydelete']);
Route::post('/categories/filter', [CategoriesController::class, 'filter']);
Route::post('/categories/update', [CategoriesController::class, 'update']);

/*************************************************************************/

/****************************** Tag Crud *********************************/
Route::get('/tags', [TagsController::class, 'index']);
Route::post('/tags/create', [TagsController::class, 'create']);
Route::delete('/tags/delete/{id}', [TagsController::class, 'delete']);
Route::delete('/tags/permanentlydelete/{id}', [TagsController::class, 'permanentlydelete']);
Route::post('/tags/filter', [TagsController::class, 'filter']);
Route::post('/tags/update', [TagsController::class, 'update']);

/*************************************************************************/
