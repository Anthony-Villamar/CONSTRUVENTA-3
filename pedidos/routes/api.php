<?php


// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;


Route::get('/ping', function () {
  return response()->json(['mensaje' => 'Pedidos UP']);
});

#crear pedido
Route::post('/pedidos', [PedidoController::class, 'crear']);
#consultar pedido
Route::get('/pedidos/{id_pedido}', [PedidoController::class, 'consultarPedido']);
Route::get('/pedidos/usuario/{usuario_id}/por-global', [PedidoController::class, 'listarPorGlobal']);
Route::get('/pedidos/usuario/{usuario_id}', [PedidoController::class, 'listarPorUsuario']);
