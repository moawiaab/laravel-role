<?php

use Illuminate\Support\Facades\Route;


Route::get('/pk', function(){
    dd(auth()->user());
});