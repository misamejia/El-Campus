<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medio extends Model
{
    protected $table = 'medios';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'subido_por',
        'disco',
        'ruta',
        'tipo_mime',
        'tamano_bytes',
        'ancho',
        'alto',
        'texto_alternativo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'tamano_bytes' => 'integer',
        'ancho' => 'integer',
        'alto' => 'integer',
    ];

    /**
     * Usuario que subió el archivo.
     */
    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /**
     * Ruta pública lista para usar con asset().
     */
    public function getUrlAttribute(): string
    {
        return (string) $this->ruta;
    }

    /**
     * Registra (o recupera) un medio a partir de su ruta pública.
     *
     * Útil para imágenes incluidas con el sitio y para el sembrado de datos.
     * Si el archivo existe dentro de public/, toma su tamaño real; de lo
     * contrario usa un tamaño mínimo válido.
     */
    public static function desdeRuta(?string $ruta, ?int $subidoPor = null): ?self
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        $archivo = public_path($ruta);
        $tamano = is_file($archivo) ? max(1, (int) filesize($archivo)) : 1;

        return static::firstOrCreate(
            ['disco' => 'public', 'ruta' => $ruta],
            [
                'subido_por' => $subidoPor,
                'tipo_mime' => static::mimeDe($ruta),
                'tamano_bytes' => $tamano,
            ]
        );
    }

    /**
     * Deduce el tipo MIME a partir de la extensión del archivo.
     */
    public static function mimeDe(string $ruta): string
    {
        return match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class, 'imagen_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'imagen_id');
    }
}
