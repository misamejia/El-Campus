<?php

namespace Tests;

use App\Models\EstadoEvento;
use App\Models\EstadoNoticia;
use App\Models\Medio;
use App\Models\Organizador;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Elimina vistas al refrescar la base de datos para evitar el error 1050.
     */
    protected $dropViews = true;

    /**
     * Impide que la suite MySQL se ejecute sobre una base que no sea de pruebas.
     */
    protected function setUpTraits()
    {
        $conexion = (string) config('database.default');

        self::validarBaseDeDatosDePrueba(
            (string) config("database.connections.{$conexion}.driver"),
            config("database.connections.{$conexion}.database"),
        );

        return parent::setUpTraits();
    }

    /**
     * Valida el motor y el nombre de la base antes de migrar o borrar datos.
     */
    public static function validarBaseDeDatosDePrueba(string $driver, ?string $base): void
    {
        $base = trim((string) $base);

        if ($driver === 'mysql' && ! str_ends_with($base, '_test')) {
            throw new RuntimeException(
                "La suite MySQL solo puede ejecutarse sobre una base terminada en _test; base configurada: '{$base}'."
            );
        }
    }

    /**
     * Identificador del estado de noticia a partir de su codigo.
     */
    protected function idEstadoNoticia(string $codigo): int
    {
        return (int) EstadoNoticia::where('codigo', $codigo)->value('id');
    }

    /**
     * Identificador del estado de evento a partir de su codigo.
     */
    protected function idEstadoEvento(string $codigo): int
    {
        return (int) EstadoEvento::where('codigo', $codigo)->value('id');
    }

    /**
     * Identificador del organizador, creándolo si no existe.
     */
    protected function idOrganizador(string $nombre): int
    {
        return (int) Organizador::firstOrCreate(['nombre' => $nombre])->id;
    }

    /**
     * Identificador del medio asociado a una ruta pública de imagen.
     */
    protected function idMedio(string $ruta): int
    {
        return (int) Medio::desdeRuta($ruta)->id;
    }
}
