<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Article routes
    Route::post('/dashboard', function () {
        // This will be handled by the Volt component
        return redirect()->route('dashboard');
    })->name('articles.store');

    Route::delete('/dashboard/articles/{article}', function (App\Models\Article $article) {
        $user = auth()->user();
        if ($article->user_id !== $user->id) {
            abort(403);
        }

        $article->delete();

        return redirect()->route('dashboard');
    })->name('articles.destroy');
});

require __DIR__.'/auth.php';
