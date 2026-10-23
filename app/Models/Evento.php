<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    /**
     * Códigos de estado visibles en el sitio público.
     *
     * El código del estado es la fuente de verdad; la columna es_publico de
     * estados_evento solo se usa como espejo para las vistas SQL.
     *
     * @var array<int, string>
     */
    public const ESTADOS_PUBLICOS = ['programado', 'realizado'];

    protected $table = 'eventos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organizador_id',
        'estado_id',
        'imagen_id',
        'creado_por',
        'actualizado_por',
        'titulo',
        'slug',
        'descripcion',
        'lugar',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * Dependencia que organiza el evento.
     */
    public function organizador(): BelongsTo
    {
        return $this->belongsTo(Organizador::class, 'organizador_id');
    }

    /**
     * Estado del evento dentro de la agenda.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoEvento::class, 'estado_id');
    }

    /**
     * Imagen asociada al evento.
     */
    public function medio(): BelongsTo
    {
        return $this->belongsTo(Medio::class, 'imagen_id');
    }

    /**
     * Usuario que registró el evento.
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Último usuario que modificó el evento.
     */
    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    /**
     * Bitácora de cambios de estado.
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoEvento::class, 'evento_id')->orderByDesc('cambiado_en');
    }

    /**
     * Ruta pública de la imagen (o null si no tiene).
     */
    public function getImagenAttribute(): ?string
    {
        return $this->medio?->ruta;
    }

    /**
     * Texto alternativo de la imagen (o null si no se definió).
     */
    public function getImagenAltAttribute(): ?string
    {
        return $this->medio?->texto_alternativo;
    }

    /**
     * Eventos visibles en el sitio público (estados "programado" y "realizado").
     */
    public function scopePublicos(Builder $query): Builder
    {
        return $query->whereHas('estado', fn (Builder $estado) => $estado->whereIn('codigo', self::ESTADOS_PUBLICOS));
    }

    /**
     * Filtra por el codigo del estado (programado, realizado, cancelado).
     */
    public function scopeDelEstado(Builder $query, string $codigo): Builder
    {
        return $query->whereHas('estado', fn (Builder $estado) => $estado->where('codigo', $codigo));
    }
}
