<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;

Route::get('/', function () {
    return view('welcome');
});

//registrar pedido
Route::post('/productos', [ProductoController::class, 'registrar']);
Route::get('/productos', [ProductoController::class, 'listar']);
//consultar disponibilidad de productos
Route::get('/productos/{codigo_producto}', [ProductoController::class, 'consultar']);
#actualizar stock
Route::put('/productos/{codigo_producto}/existencias', [ProductoController::class, 'actualizarExistencias']);
#generar alerta
Route::get('/alerta-stock', [ProductoController::class, 'alertaStock']);
#reabastecer producto
Route::put('/productos/{codigo_producto}/reabastecer', [ProductoController::class, 'reabastecer']);
#actualizar producto
Route::put('/productos/{codigo_producto}', [ProductoController::class, 'actualizar']);
