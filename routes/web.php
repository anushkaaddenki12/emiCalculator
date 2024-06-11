<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanDetailsController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/loan_details', [LoanDetailsController::class, 'index'])->name('loan_details.index');
Route::post('/loan_details/process', [LoanDetailsController::class, 'process'])->name('loan_details.process');
