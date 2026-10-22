<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoEvento extends Model
{
    protected $table = 'historial_estados_evento';

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'evento_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'usuario_id',
        'cambiado_en',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cambiado_en' => 'datetime',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoEvento::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoEvento::class, 'estado_nuevo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
