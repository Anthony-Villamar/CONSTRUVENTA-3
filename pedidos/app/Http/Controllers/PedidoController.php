<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoController extends Controller
{
    #CREAR PEDIDO()
    public function crear(Request $request)
    {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
            \Log::error('Datos incompletos');
            return response()->json(['mensaje' => 'Datos incompletos'], 400);
        }

        $usuario_id = $data['usuario_id'];
        $productos = $data['productos'];

        $response = Http::get("https://construventa-2-36ul.onrender.com/usuarios/" . $usuario_id);

        if ($response->failed()) {
            \Log::error('Usuario no encontrado');
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario = $response->json();
        \Log::info('Usuario encontrado', $usuario);

        $ids_pedidos = [];

        // 🔥 Genera el primer id_pedido, que será también el id_pedido_global
        $id_pedido_global = Str::uuid();

        $primer_producto = true;

        foreach ($productos as $producto) {
            \Log::info('Procesando producto', $producto);

            // 🔥 Usa el primer id_pedido como global
            if ($primer_producto) {
                $id_pedido = $id_pedido_global;
                $primer_producto = false;
            } else {
                $id_pedido = Str::uuid();
            }

            \Log::info('ID pedido generado: ' . $id_pedido);

            try {
                DB::table('pedido')->insert([
                    'id_pedido' => $id_pedido,
                    'id_pedido_global' => $id_pedido_global, // 🔥 asigna el mismo global
                    'id_cliente' => $usuario['cedula'],
                    'producto' => $producto['codigo_producto'],
                    'cantidad' => $producto['cantidad'],
                    'fecha_pedido' => now(),
                    'direccion_entrega' => $usuario['direccion'],
                    'zona_entrega' => $usuario['zona']
                ]);
                \Log::info('Pedido insertado correctamente');

                $ids_pedidos[] = $id_pedido;

                // Actualizar inventario
                $dataUpdate = ["cantidad" => $producto['cantidad']];
                $opts = [
                    "http" => [
                        "method" => "PUT",
                        "header" => "Content-Type: application/json",
                        "content" => json_encode($dataUpdate)
                    ]
                ];
                $context = stream_context_create($opts);
                $result = @file_get_contents("https://inventario-gfxs.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", false, $context);

                \Log::info('Actualización de inventario', [$result]);
            } catch (\Exception $e) {
                \Log::error('Error al procesar pedido: ' . $e->getMessage());
                return response()->json(['mensaje' => 'Error interno al procesar pedido'], 500);
            }
        }

        return response()->json([
            'mensaje' => 'Pedido(s) creados correctamente',
            'ids_pedidos' => $ids_pedidos,
            'id_pedido_global' => $id_pedido_global
        ]);
    }

    # CONSULTAR PEDIDO()
    public function consultarPedido($id_pedido)
    {
        $pedido = DB::table('pedido')->where('id_pedido', $id_pedido)->first();

        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }
    public function listarPorUsuario($usuario_id)
    {
        try {
            $pedidos = DB::table('pedido')
                ->join('productos', 'pedido.producto', '=', 'productos.codigo_producto')
                ->select(
                    'pedido.id_pedido',
                    DB::raw("DATE_FORMAT(fecha_pedido, '%Y-%m-%d %H:%i') as hora_compra"),
                    DB::raw("GROUP_CONCAT(CONCAT(productos.nombre, ' x', pedido.cantidad) SEPARATOR ', ') as productos")
                )
                ->where('id_cliente', $usuario_id)
                ->groupBy('pedido.id_pedido', 'hora_compra')
                ->orderBy('hora_compra', 'desc')
                ->get();

            return response()->json($pedidos);

        } catch (Exception $e) {
            \Log::error('Error en listarPorUsuario', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function listarPorGlobal($usuario_id)
{
    try {
        // 🔥 Obtiene pedidos
        $pedidos = DB::table('pedido')
            ->join('producto', 'pedido.producto', '=', 'producto.codigo_producto')
            ->select(
                'pedido.id_pedido_global',
                DB::raw("MAX(fecha_pedido) as fecha_compra"),
                DB::raw("GROUP_CONCAT(CONCAT(producto.nombre, ' x', pedido.cantidad) SEPARATOR ', ') as productos")
            )
            ->where('id_cliente', $usuario_id)
            ->groupBy('pedido.id_pedido_global')
            ->orderBy('fecha_compra', 'desc')
            ->get();

        // 🔥 Obtiene facturas desde tu microservicio de facturación
        $facturas = Http::get("https://facturacion-cqr4.onrender.com/facturas/usuario/" . $usuario_id)->json();

        // 🔥 Asigna el total de la factura a cada pedido global
        foreach ($pedidos as $p) {
            $factura = collect($facturas)->firstWhere('id_pedido', $p->id_pedido_global);
            $p->total_compra = $factura['total'] ?? null;
            $p->numero_factura = $factura['id_factura'] ?? null;

        }

        return response()->json($pedidos);

    } catch (Exception $e) {
        \Log::error('Error en listarPorGlobal', ['message' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
