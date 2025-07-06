<?php


// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;

Route::post('/pedidos', [PedidoController::class, 'crear']);
Route::get('/pedidos/{id_pedido}', [PedidoController::class, 'consultarPedido']);
Route::get('/ping', function () {
  return response()->json(['mensaje' => 'Pedidos UP']);
});

Route::get('/pedidos/usuario/{usuario_id}', [PedidoController::class, 'listarPorUsuario']);
Route::get('/pedidos/pendientes', [PedidoController::class, 'listarPedidosPendientes']);
Route::put('/productos/{codigo_producto}/reabastecer', [ProductoController::class, 'reabastecer']);
