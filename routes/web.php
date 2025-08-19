<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Volt;
use App\Http\Controllers\PdfController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('screenings');
    } else {
        return redirect()->route('login');
    }
});

Route::middleware(['auth'])->group(function () {
    Volt::route('screening', 'admin.screening')->name('screenings');
    Volt::route('history', 'admin.report')->name('reports');
    Volt::route('profile', 'admin.profile')->name('profile');
    Volt::route('/screening/result/{screening}', 'admin.result')->name('screening.result');

    // PDF Download Routes
    Route::get('/screening/pdf/{screening}', [PdfController::class, 'downloadScreeningReport'])
        ->name('screening.pdf');
});


require __DIR__ . '/auth.php';
