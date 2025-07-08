<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoController extends Controller
{
    // public function crear(Request $request)
    // {
    //     \Log::info('==> Inicia método crear()');
    
    //     $data = json_decode(file_get_contents("php://input"), true);
    //     \Log::info('Datos recibidos', $data);
    
    //     if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
    //         \Log::error('Datos incompletos');
    //         return response()->json(['mensaje' => 'Datos incompletos'], 400);
    //     }
    
    //     $usuario_id = $data['usuario_id'];
    //     $productos = $data['productos'];
    
    //     \Log::info('Consultando usuario: ' . $usuario_id);
    //     $response = Http::get("https://usuarios-1yw0.onrender.com/usuarios/" . $usuario_id);
    
    //     if ($response->failed()) {
    //         \Log::error('Usuario no encontrado');
    //         return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
    //     }
    
    //     $usuario = $response->json();
    //     \Log::info('Usuario encontrado', $usuario);
    
    //     $ids_pedidos = [];
    
    //     foreach ($productos as $producto) {
    //         \Log::info('Procesando producto', $producto);
    
    //         $id_pedido = Str::uuid();
    //         \Log::info('ID pedido generado: ' . $id_pedido);
    
    //         try {
    //             DB::table('pedido')->insert([
    //                 'id_pedido' => $id_pedido,
    //                 'id_cliente' => $usuario['cedula'],
    //                 'producto' => $producto['codigo_producto'],
    //                 'cantidad' => $producto['cantidad'],
    //                 'fecha_pedido' => now(),
    //                 'direccion_entrega' => $usuario['direccion'],
    //                 'zona_entrega' => $usuario['zona']
    //             ]);
    //             \Log::info('Pedido insertado correctamente');
    
    //             $ids_pedidos[] = $id_pedido;
    
    //             $dataUpdate = ["cantidad" => $producto['cantidad']];
    //             $opts = [
    //                 "http" => [
    //                     "method" => "PUT",
    //                     "header" => "Content-Type: application/json",
    //                     "content" => json_encode($dataUpdate)
    //                 ]
    //             ];
    //             $context = stream_context_create($opts);
    //             $result = @file_get_contents("https://inventario-d5am.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", false, $context);
    
    //             \Log::info('Actualización de inventario', [$result]);
    //         } catch (\Exception $e) {
    //             \Log::error('Error al procesar pedido: ' . $e->getMessage());
    //             return response()->json(['mensaje' => 'Error interno al procesar pedido'], 500);
    //         }
    //     }
    
    //     return response()->json([
    //         'mensaje' => 'Pedido(s) creados correctamente',
    //         'ids_pedidos' => $ids_pedidos
    //     ]);
    // }

    public function crear(Request $request)
{
    \Log::info('==> Inicia método crear()');

    $data = json_decode(file_get_contents("php://input"), true);
    \Log::info('Datos recibidos', $data);

    if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
        \Log::error('Datos incompletos');
        return response()->json(['mensaje' => 'Datos incompletos'], 400);
    }

    $usuario_id = $data['usuario_id'];
    $productos = $data['productos'];

    \Log::info('Consultando usuario: ' . $usuario_id);
    $response = Http::get("https://usuarios-1yw0.onrender.com/usuarios/" . $usuario_id);

    if ($response->failed()) {
        \Log::error('Usuario no encontrado');
        return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
    }

    $usuario = $response->json();
    \Log::info('Usuario encontrado', $usuario);

    $ids_pedidos = [];

    // 🔥 Genera un id_pedido_global para todo el pedido
    $id_pedido_global = Str::uuid();

    foreach ($productos as $producto) {
        \Log::info('Procesando producto', $producto);

        $id_pedido = Str::uuid();
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
            $result = @file_get_contents("https://inventario-d5am.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", false, $context);

            \Log::info('Actualización de inventario', [$result]);
        } catch (\Exception $e) {
            \Log::error('Error al procesar pedido: ' . $e->getMessage());
            return response()->json(['mensaje' => 'Error interno al procesar pedido'], 500);
        }
    }

    return response()->json([
        'mensaje' => 'Pedido(s) creados correctamente',
        'ids_pedidos' => $ids_pedidos,
        'id_pedido_global' => $id_pedido_global // 🔥 devuelve si deseas usarlo en frontend
    ]);
}

    
// public function crear(Request $request)
// {
//     \Log::info('==> Inicia método crear()');

//     // $data = json_decode(file_get_contents("php://input"), true);
//     $data = $request->all();
//     \Log::info('Datos recibidos', $data);

//     if (!$data || !isset($data['usuario_id']) || !isset($data['productos'])) {
//         \Log::error('Datos incompletos');
//         return response()->json(['mensaje' => 'Datos incompletos'], 400);
//     }

//     $usuario_id = $data['usuario_id'];
//     $productos = $data['productos'];

//     \Log::info('Consultando usuario: ' . $usuario_id);
//     $response = Http::get("https://usuarios-1yw0.onrender.com/usuarios/" . $usuario_id);

//     if ($response->failed()) {
//         \Log::error('Usuario no encontrado');
//         return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
//     }

//     $usuario = $response->json();
//     \Log::info('Usuario encontrado', $usuario);

//     $id_pedido = Str::uuid(); // ✅ Generar solo una vez

//     try {
//     foreach ($productos as $producto) {
//         $id_pedido = Str::uuid(); // 🔄 Generar ID único en cada loop

//         DB::table('pedido')->insert([
//             'id_pedido' => $id_pedido,
//             'id_cliente' => $usuario['cedula'],
//             'producto' => $producto['codigo_producto'],
//             'cantidad' => $producto['cantidad'],
//             'fecha_pedido' => now(),
//             'direccion_entrega' => $usuario['direccion'],
//             'zona_entrega' => $usuario['zona']
//         ]);


//             \Log::info('Pedido insertado correctamente');

//             // Actualizar inventario
//             $dataUpdate = ["cantidad" => $producto['cantidad']];
//             $opts = [
//                 "http" => [
//                     "method" => "PUT",
//                     "header" => "Content-Type: application/json",
//                     "content" => json_encode($dataUpdate)
//                 ]
//             ];
//             $context = stream_context_create($opts);
//             $result = @file_get_contents("https://inventario-d5am.onrender.com/api/productos/{$producto['codigo_producto']}/existencias", false, $context);

//             \Log::info('Actualización de inventario', [$result]);
//         }

//         return response()->json([
//     'mensaje' => 'Pedidos creados correctamente',
//     'ids_pedidos' => $ids_pedidos
// ]);


//     } catch (\Exception $e) {
//         \Log::error('Error al procesar pedido: ' . $e->getMessage());
//         return response()->json(['mensaje' => 'Error interno al procesar pedido'], 500);
//     }
// }


    public function consultarPedido($id_pedido)
    {
        $pedido = DB::table('pedido')->where('id_pedido', $id_pedido)->first();

        if (!$pedido) {
            return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    // public function listarPorUsuario(Request $request, $usuario_id)
    // {
    //     try {
    //         $query = DB::table('pedido')->where('id_cliente', $usuario_id);

    //         if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
    //             $query->whereBetween('fecha_pedido', [
    //                 $request->fecha_inicio . ' 00:00:00',
    //                 $request->fecha_fin . ' 23:59:59'
    //             ]);
    //         }

    //         $pedidos = $query->orderBy('fecha_pedido', 'desc')->get();

    //         return response()->json($pedidos);

    //     } catch (Exception $e) {
    //         \Log::error('Error en listarPorUsuario', ['message' => $e->getMessage()]);
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

//     public function listarPorUsuario(Request $request, $usuario_id)
// {
//     try {
//         $query = DB::table('pedido')
//             ->join('producto', 'pedido.producto', '=', 'producto.codigo_producto')
//             ->where('id_cliente', $usuario_id)
//             ->select('pedido.*', 'producto.nombre as nombre_producto');

//         if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
//             $query->whereBetween('fecha_pedido', [
//                 $request->fecha_inicio . ' 00:00:00',
//                 $request->fecha_fin . ' 23:59:59'
//             ]);
//         }

//         $pedidos = $query->orderBy('fecha_pedido', 'desc')->get();

//         return response()->json($pedidos);

//     } catch (Exception $e) {
//         \Log::error('Error en listarPorUsuario', ['message' => $e->getMessage()]);
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }
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


public function listarAgrupadosPorHora($usuario_id)
{
    try {
        $pedidos = DB::table('pedido')
             ->join('producto', 'pedido.producto', '=', 'producto.codigo_producto')
            ->select(
                DB::raw("DATE_FORMAT(fecha_pedido, '%Y-%m-%d %H:00') as hora_compra"),
                DB::raw("MIN(pedido.id_pedido) as primer_id_pedido"),
                DB::raw("GROUP_CONCAT(CONCAT(producto.nombre, ' x', pedido.cantidad) SEPARATOR ', ') as productos")
            )
            ->where('id_cliente', $usuario_id)
            ->groupBy('hora_compra')
            ->orderBy('hora_compra', 'desc')
            ->get();

        return response()->json($pedidos);

    } catch (Exception $e) {
        \Log::error('Error en listarAgrupadosPorHora', ['message' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    
    public function listarPedidosPendientes()
{
    $pedidos = DB::table('pedido')->get(); // si deseas filtrar, usa where()
    return response()->json($pedidos);
}

    
    
}
