<?php


// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;

Route::post('/pedidos', [PedidoController::class, 'crear']);
Route::get('/pedidos/{id_pedido}', [PedidoController::class, 'consultarPedido']);
