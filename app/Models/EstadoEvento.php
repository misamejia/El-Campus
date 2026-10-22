<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoEvento extends Model
{
    protected $table = 'estados_evento';

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
     * Un estado agrupa a muchos eventos.
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'estado_id');
    }
}
