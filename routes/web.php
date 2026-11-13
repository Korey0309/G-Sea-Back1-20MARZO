<?php

use App\Support\AseguradoraPagoLinks;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'G-SEA API']);
});

/** Botón WhatsApp "Pagar mi póliza": /pagar/qualitas → portal de esa aseguradora. */
Route::get('/pagar/{slug}', function (string $slug) {
    return redirect()->away(AseguradoraPagoLinks::urlForSlug($slug));
})->where('slug', '[A-Za-z0-9_-]+');
