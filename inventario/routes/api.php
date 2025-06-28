<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;


Route::get('/', function () {
    return view('welcome');
});
Route::post('/productos', [ProductoController::class, 'registrar']);
Route::get('/productos', [ProductoController::class, 'listar']);
Route::get('/productos/{codigo_producto}', [ProductoController::class, 'consultar']);
Route::put('/productos/{codigo_producto}/existencias', [ProductoController::class, 'actualizarExistencias']);