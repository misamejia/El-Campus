<?php

namespace Tests;

use App\Models\EstadoEvento;
use App\Models\EstadoNoticia;
use App\Models\Medio;
use App\Models\Organizador;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
