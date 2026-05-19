<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $fillable = ['nombre', 'fecha_inicio', 'fecha_fin', 'estado', 'gestion'];

    public function lecturas()
    {
        return $this->hasMany(Lectura::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
