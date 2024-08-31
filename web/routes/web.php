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

Route::get('/hosting-subscription/visit-local', function () {

    $domain = request()->get('domain');
    $findDomain = \App\Models\Domain::where('domain', $domain)->first();
    if (!$findDomain) {
        return response()->json(['error' => 'Domain not found'], 404);
    }
    $etcHosts = file_get_contents('/etc/hosts');
    if (!Str::contains($etcHosts, $findDomain->domain)) {
        shell_exec('sudo echo "0.0.0.0 '.$findDomain->domain.'" | sudo tee -a /etc/hosts');
    }

    $websiteContent = shell_exec('curl -s http://'.$findDomain->domain);

    echo $websiteContent;

})->name('hosting-subscription.visit-local');


Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::prefix('/file-manager')->controller(\App\Http\Controllers\FileManager\FileManagerController::class)->group(function () {

    Route::get('/initialize', 'initialize');
    Route::get('/tree', 'tree');
    Route::get('/content', 'content');
    Route::post('/upload', 'upload');
    Route::post('/create-file', 'createFile');
    Route::post('/update-file', 'updateFile');
    Route::post('/create-directory', 'createDirectory');
    Route::post('/delete', 'delete');
    Route::post('/paste', 'paste');
    Route::post('/rename', 'rename');
    Route::get('/download', 'download');
    Route::get('/preview', 'preview');
    Route::get('/thumbnails', 'thumbnails');
    Route::get('/url', 'url');
    Route::get('/stream', 'streamFile');
    Route::post('zip', 'zip');
    Route::post('unzip', 'unzip');

});
