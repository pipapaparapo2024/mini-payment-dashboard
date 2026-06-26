<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $spaIndex = public_path('spa/index.html');

    if (file_exists($spaIndex)) {
        return redirect('/spa/');
    }

    return view('welcome');
});

Route::get('/spa/{any?}', function () {
    $spaIndex = public_path('spa/index.html');

    abort_unless(file_exists($spaIndex), 404);

    return response()->file($spaIndex);
})->where('any', '.*');
