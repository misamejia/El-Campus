<?php

namespace App\Http\Controllers;

use App\Models\EstadoEvento;
use App\Models\Evento;
use App\Models\Organizador;
use App\Services\Medios\GestorMedios;
use Illuminate\Http\RedirectResponse;

class EventoController extends Controller
{
    /**
     * Elimina un evento.
     */
    public function destroy(Evento $evento): RedirectResponse
    {
        $evento->load('medio');
        $medio = $evento->medio;

        $evento->delete();
        $this->gestorMedios()->limpiar($medio);

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento eliminado correctamente.');
    }

    /**
     * Gestor de imágenes compartidas por el panel.
     */
    private function gestorMedios(): GestorMedios
    {
        return app(GestorMedios::class);
    }

    /**
     * Resuelve el id del estado de evento a partir de su codigo.
     */
    private function estadoId(string $codigo): int
    {
        return (int) EstadoEvento::where('codigo', $codigo)->value('id');
    }

    /**
     * Resuelve (o crea) el organizador a partir de su nombre.
     */
    private function organizadorId(?string $nombre): ?int
    {
        $nombre = $nombre !== null ? trim($nombre) : '';

        if ($nombre === '') {
            return null;
        }

        return Organizador::firstOrCreate(['nombre' => $nombre], ['activo' => true])->id;
    }
}
