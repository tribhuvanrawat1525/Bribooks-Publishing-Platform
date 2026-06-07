<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\DashboardController;

Route::middleware('api.log')->group(function () {

    Route::prefix('auth')->group(function () {
    
        Route::post('/register',[AuthController::class, 'register']);
        Route::post('/login',[AuthController::class, 'login']);
        Route::middleware('jwt.auth')->get('/profile',[AuthController::class,'profile']);
        Route::middleware('jwt.auth')->get('/logout',[AuthController::class,'logout']);
    });
    
    //Books
    Route::middleware('jwt.auth')->group(function () {
    
        //Books 
        Route::post('/books',[BookController::class, 'create']);
    
        Route::get('/books',[BookController::class, 'list']);
    
        Route::get('/books/{id}',[BookController::class, 'details']);
    
        Route::put('/books/{id}',[BookController::class, 'update']);
    
        Route::delete('/books/{id}',[BookController::class, 'delete']);
    
    
        //Version
        Route::post('/books/{bookId}/versions',[VersionController::class, 'create']);
    
        Route::get('/books/{bookId}/versions',[VersionController::class, 'list']);
    
        Route::get('/books/{bookId}/versions/{versionId}',[VersionController::class, 'details']);
    
        //Chapters
        Route::post('/books/{bookId}/chapters',[ChapterController::class, 'create']);
    
        Route::get('/books/{bookId}/chapters',[ChapterController::class, 'list']);
    
        Route::put('/chapters/{chapterId}',[ChapterController::class, 'update']);
    
        Route::delete('/chapters/{chapterId}',[ChapterController::class, 'delete']);
    
        //Pages
        Route::post('/chapters/{chapterId}/pages',[PageController::class, 'create']);
    
        Route::get('/chapters/{chapterId}/pages',[PageController::class, 'list']);
    
        Route::put('/pages/{pageId}',[PageController::class, 'update']);
    
        Route::delete('/pages/{pageId}',[PageController::class, 'delete']);
    
        //Workflow
        Route::post('/books/{bookId}/submit',[WorkflowController::class, 'submit']);
    
        Route::post('/books/{bookId}/review',[WorkflowController::class, 'review']);
    
        Route::post('/books/{bookId}/approve',[WorkflowController::class, 'approve']);
    
        Route::post('/books/{bookId}/reject',[WorkflowController::class, 'reject']);
        
        Route::post('/books/{bookId}/publish',[WorkflowController::class, 'publish']);

        //Restore
        Route::post('/books/{bookId}/versions/{versionId}/restore',[VersionController::class, 'restore']);

        //upload doc
        Route::post('/books/{id}/upload',[BookController::class, 'upload']);

        //Dashboard
        Route::get('/dashboard',[DashboardController::class, 'stats']);
    
    });
    
});


