<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoController extends Controller
{
    public function crear(Request $request)
    {
        try {
            // ✅ Usar request->input en lugar de json_decode php://input
            $data = $request->input();

            if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
                return response()->json(['mensaje' => 'Datos incompletos'], 400);
            }

            \Log::info('Pedido recibido', $data);

            $usuario_id = $data['usuario_id'];
            $productos = $data['productos'];

            // 🔧 Obtener usuario con Http::get y manejo de error
            $response = Http::timeout(5)->get("https://usuarios-1yw0.onrender.com/usuarios/" . $usuario_id);

            if ($response->failed()) {
                \Log::error('Usuario no encontrado o microservicio caído', ['usuario_id' => $usuario_id]);
                return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
            }

            $usuario = $response->json();

            // ✅ Validar campos necesarios del usuario
            if (!isset($usuario['cedula'], $usuario['direccion'], $usuario['zona'])) {
                \Log::error('Usuario con datos incompletos', $usuario);
                return response()->json(['mensaje' => 'Datos incompletos en usuario'], 500);
            }

            $ids_pedidos = [];

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

                $ids_pedidos[] = $id_pedido;

                // ✅ Actualizar stock del producto con Http::put en lugar de file_get_contents
                $updateResponse = Http::timeout(5)->put("https://inventario-d5am.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", [
                    'cantidad' => $producto['cantidad']
                ]);

                if ($updateResponse->failed()) {
                    \Log::error('Error actualizando inventario', [
                        'producto_codigo' => $producto['codigo_producto'],
                        'status' => $updateResponse->status()
                    ]);
                }
            }

            return response()->json([
                'mensaje' => 'Pedido(s) creados correctamente',
                'ids_pedidos' => $ids_pedidos
            ]);

        } catch (Exception $e) {
            \Log::error('Error en crear pedido', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['mensaje' => 'Error interno en el servidor'], 500);
        }
    }

    public function consultarPedido($id_pedido)
    {
        $pedido = DB::table('pedido')->where('id_pedido', $id_pedido)->first();

        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    public function listarPorUsuario(Request $request, $usuario_id)
    {
        try {
            $query = DB::table('pedido')->where('id_cliente', $usuario_id);

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('fecha_pedido', [
                    $request->fecha_inicio . ' 00:00:00',
                    $request->fecha_fin . ' 23:59:59'
                ]);
            }

            $pedidos = $query->orderBy('fecha_pedido', 'desc')->get();

            return response()->json($pedidos);

        } catch (Exception $e) {
            \Log::error('Error en listarPorUsuario', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
