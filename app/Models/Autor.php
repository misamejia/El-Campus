<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Autor extends Model
{
    protected $table = 'autores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'email',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un autor (persona o equipo editorial) puede tener muchas noticias.
     */
    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class, 'autor_id');
    }
}
