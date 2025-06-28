<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_pedido';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id_pedido',
        'cedula_cliente',
        'direccion_entrega',
        'zona_entrega'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }
}
