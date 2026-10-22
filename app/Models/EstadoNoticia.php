<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoNoticia extends Model
{
    protected $table = 'estados_noticia';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'es_publico',
        'orden',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'es_publico' => 'boolean',
    ];

    /**
     * Un estado agrupa a muchas noticias.
     */
    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class, 'estado_id');
    }
}
