<?php

use Illuminate\Support\Facades\Route;

Route::get('login', function(){
    return response()->json([
        'code'      =>  404,
        'messages'  =>  [ 'Invalid Endpoint' ],
        'data'      =>  null
    ], 200);
})->name('login');


Route::fallback(function(){
    return response()->json([
        'code'      =>  404,
        'messages'  =>  [ 'Invalid Endpoint' ],
        'data'      =>  null
    ], 200);
});

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/change-pass', function (){
//
//    try {
//        $user = \App\Domain\UserRelation\Models\User::where('mobile_no', '+9641829298814')->first();
//        $user->password = \Hash::make('Password100@');
//        $user->update();
//    }catch (\Exception $e){
//        \Log::error($e);
//    }
//
//
//});
