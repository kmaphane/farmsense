<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// All authenticated routes are now in Filament at /admin
// Settings, dashboard, and admin functionality moved to Filament
