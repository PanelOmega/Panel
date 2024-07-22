<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customer/phpMyAdmin/login', [\App\Http\Controllers\Customer\PHPMyAdminController::class, 'login'])
    ->name('customer.phpmyadmin.login');


Route::get('/customer/file-manager', function () {
    return view('customer.pages.file-manager');
});


Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::prefix('/file-manager')->controller(\App\Http\Controllers\FileManager\FileManagerController::class)->group(function () {

    Route::GET('/initialize', 'initialize');
    Route::GET('/tree', 'tree');
    Route::GET('/content', 'content');
    Route::POST('/upload', 'upload');
    Route::POST('/create-file', 'createFile');
    Route::POST('/update-file', 'updateFile');
    Route::POST('/create-directory', 'createDirectory');
    Route::POST('/delete', 'delete');
    Route::POST('/paste', 'paste');
    Route::POST('/rename', 'rename');
    Route::GET('/download', 'download');
    Route::GET('/preview', 'preview');
    Route::GET('/thumbnails', 'thumbnails');
    Route::GET('/url', 'url');
    Route::GET('/stream', 'streamFile');
    Route::POST('zip', 'zip');
    Route::POST('unzip', 'unzip');

});

Route::get('/customers/ftp-connections', [\App\Http\Controllers\Customer\FtpConnections\FtpConnectionsController::class, 'index'])->name('ftp-connections.index');
