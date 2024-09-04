<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo si el nombre es diferente a la convención pluralizada.
    protected $table = 'ACCOUNTS';

    // Especificar los campos que pueden ser asignados masivamente
    protected $fillable = [
        'IDOrigen',
        'IDDestino',
        'name',
    ];
}
