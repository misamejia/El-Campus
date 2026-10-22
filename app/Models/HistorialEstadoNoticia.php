<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoNoticia extends Model
{
    protected $table = 'historial_estados_noticia';

    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'noticia_id',
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

    public function noticia(): BelongsTo
    {
        return $this->belongsTo(Noticia::class, 'noticia_id');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoNoticia::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoNoticia::class, 'estado_nuevo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
