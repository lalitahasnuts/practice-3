<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OsdrController;
use App\Http\Controllers\IssController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\AstroController;

Route::get('/', fn() => redirect('/dashboard'));

// Панели / контексты
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/iss',       [IssController::class,       'index']);
Route::get('/jwst',      [DashboardController::class, 'jwst']);
Route::get('/astro',     [AstroController::class,     'page']);
Route::get('/osdr',      [OsdrController::class,      'index']);

// Прокси к rust_iss
Route::get('/api/iss/last',  [ProxyController::class, 'last']);
Route::get('/api/iss/trend', [ProxyController::class, 'trend']);

// JWST галерея (JSON)
Route::get('/api/jwst/feed', [DashboardController::class, 'jwstFeed']);
Route::get('/api/astro/events', [AstroController::class, 'events']);

// CMS
Route::get('/page/{slug}', [CmsController::class, 'page']);
