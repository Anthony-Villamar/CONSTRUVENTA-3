<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function crear(Request $request)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
        return response()->json(['mensaje' => 'Datos incompletos'], 400);
    }

    \Log::info('Pedido recibido', $data);

    $usuario_id = $data['usuario_id'];
    $productos = $data['productos'];

    // Obtener usuario
    $usuarioRes = @file_get_contents("https://usuarios-1yw0.onrender.com/usuarios/" . $usuario_id);
    $usuario = json_decode($usuarioRes, true);

    if (!$usuario) {
        return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
    }

    $ids_pedidos = []; // 📝 Arreglo para guardar los ids generados

    foreach ($productos as $producto) {
        $id_pedido = Str::uuid();

        DB::table('pedido')->insert([
            'id_pedido' => $id_pedido,
            'id_cliente' => $usuario['cedula'],
            'producto' => $producto['codigo_producto'],
            'cantidad' => $producto['cantidad'],
            'fecha_pedido' => now(),
            'direccion_entrega' => $usuario['direccion'],
            'zona_entrega' => $usuario['zona']
        ]);

        // Guardar id_pedido creado
        $ids_pedidos[] = $id_pedido;

        // Actualizar stock del producto
        $dataUpdate = ["cantidad" => $producto['cantidad']];
        $opts = [
            "http" => [
                "method" => "PUT",
                "header" => "Content-Type: application/json",
                "content" => json_encode($dataUpdate)
            ]
        ];
        $context = stream_context_create($opts);
        @file_get_contents("https://inventario-d5am.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", false, $context);
    }

    return response()->json([
        'mensaje' => 'Pedido(s) creados correctamente',
        'ids_pedidos' => $ids_pedidos
    ]);
}


    public function consultarPedido($id_pedido)
    {
        $pedido = DB::table('pedido')->where('id_pedido', $id_pedido)->first();

        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }
}
