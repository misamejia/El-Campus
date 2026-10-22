<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizador extends Model
{
    protected $table = 'organizadores';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'activo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un organizador puede tener muchos eventos.
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'organizador_id');
    }
}
