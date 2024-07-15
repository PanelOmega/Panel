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
