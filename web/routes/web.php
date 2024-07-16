<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/customer/file-manager', function () {
    return view('customer.pages.file-manager');
});


Route::get('/login', function () {
    return redirect('/');
})->name('login');


Route::get('/file-manager/initialize', [FileManagerController::class, 'initialize']);

Route::get('/file-manager/tree', [FileManagerController::class, 'tree']);

Route::get('/file-manager/content', [FileManagerController::class, 'content']);
