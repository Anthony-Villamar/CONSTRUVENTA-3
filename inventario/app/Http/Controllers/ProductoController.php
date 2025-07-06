<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function registrar(Request $request)
{
    $validator = Validator::make($request->all(), [
        'codigo_producto' => 'required|string|max:10|unique:producto,codigo_producto',
        'nombre' => 'required|string|max:50',
        'descripcion' => 'nullable|string',
        'categoria' => 'required|string|max:50',
        'precio' => 'required|numeric',
        'stock' => 'required|integer',
        'peso_kg' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // ✅ Redondea antes de insertar
    $request->merge([
        'precio' => number_format($request->precio, 2, '.', ''),
        'peso_kg' => number_format($request->peso_kg, 2, '.', '')
    ]);

    DB::table('producto')->insert([
        'codigo_producto' => $request->codigo_producto,
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
        'categoria' => $request->categoria,
        'precio' => $request->precio,
        'stock' => $request->stock,
        'peso_kg' => $request->peso_kg,
    ]);

    return response()->json(['mensaje' => 'Producto registrado correctamente']);
}

    public function consultar($codigo_producto)
    {
        $producto = DB::table('producto')->where('codigo_producto', $codigo_producto)->first();

        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    public function listar()
    {
        $productos = DB::table('producto')->get();
        return response()->json($productos);
    }

    public function actualizarExistencias(Request $request, $codigo_producto)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $producto = DB::table('producto')->where('codigo_producto', $codigo_producto)->first();

        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        $stock_actual = $producto->stock;
        $cantidad_a_restar = $request->cantidad;
        $nuevo_stock = $stock_actual - $cantidad_a_restar;

        if ($nuevo_stock < 0) {
            return response()->json(['mensaje' => 'Stock insuficiente'], 400);
        }

        DB::table('producto')->where('codigo_producto', $codigo_producto)->update([
            'stock' => $nuevo_stock
        ]);

        return response()->json([
            'mensaje' => 'Existencias actualizadas',
            'stock_anterior' => $stock_actual,
            'cantidad_vendida' => $cantidad_a_restar,
            'nuevo_stock' => $nuevo_stock
        ]);
    }

    // 🔔 NUEVO MÉTODO: Alerta de productos con stock menor a 15
    public function alertaStock()
    {
        $productos = DB::table('producto')->where('stock', '<', 15)->get();

        return response()->json([
            'mensaje' => 'Productos con bajo stock',
            'productos' => $productos
        ]);
    }

    public function reabastecer(Request $request, $codigo_producto)
{
    $validator = Validator::make($request->all(), [
        'cantidad' => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $producto = DB::table('producto')->where('codigo_producto', $codigo_producto)->first();

    if (!$producto) {
        return response()->json(['mensaje' => 'Producto no encontrado'], 404);
    }

    $stock_actual = $producto->stock;
    $cantidad_a_sumar = $request->cantidad;
    $nuevo_stock = $stock_actual + $cantidad_a_sumar;

    DB::table('producto')->where('codigo_producto', $codigo_producto)->update([
        'stock' => $nuevo_stock
    ]);

    return response()->json([
        'mensaje' => 'Producto reabastecido',
        'stock_anterior' => $stock_actual,
        'cantidad_reabastecida' => $cantidad_a_sumar,
        'nuevo_stock' => $nuevo_stock
    ]);
}
}
