<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activa',
        'orden',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Una categoría puede tener muchas noticias.
     */
    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class, 'categoria_id');
    }
}
