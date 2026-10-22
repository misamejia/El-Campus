<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Noticia extends Model
{
    protected $table = 'noticias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'categoria_id',
        'autor_id',
        'estado_id',
        'imagen_id',
        'creado_por',
        'actualizado_por',
        'titulo',
        'slug',
        'resumen',
        'contenido',
        'destacada',
        'fecha_publicacion',
        'puntos_clave',
        'etiquetas_ia',
        'embedding',
        'embedding_actualizado_en',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'destacada' => 'boolean',
        'puntos_clave' => 'array',
        'etiquetas_ia' => 'array',
        'embedding' => 'array',
        'embedding_actualizado_en' => 'datetime',
    ];

    /**
     * Una noticia pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Una noticia pertenece a un autor (persona o equipo editorial).
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(Autor::class, 'autor_id');
    }

    /**
     * Estado editorial de la noticia.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoNoticia::class, 'estado_id');
    }

    /**
     * Imagen asociada a la noticia.
     */
    public function medio(): BelongsTo
    {
        return $this->belongsTo(Medio::class, 'imagen_id');
    }

    /**
     * Usuario que registró la noticia.
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Último usuario que modificó la noticia.
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
        return $this->hasMany(HistorialEstadoNoticia::class, 'noticia_id')->orderByDesc('cambiado_en');
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
     * Noticias visibles en el sitio público (estado con es_publico = 1).
     */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->whereHas('estado', fn (Builder $estado) => $estado->where('es_publico', true));
    }

    /**
     * Filtra por el codigo del estado (borrador, publicada, archivada...).
     */
    public function scopeDelEstado(Builder $query, string $codigo): Builder
    {
        return $query->whereHas('estado', fn (Builder $estado) => $estado->where('codigo', $codigo));
    }
}
