<?php

namespace App\Http\Controllers;

use App\Models\EstadoNoticia;
use App\Models\Noticia;
use App\Services\Medios\GestorMedios;
use App\Services\Noticias\GeneradorIa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    /**
     * Elimina una noticia.
     */
    public function destroy(Noticia $noticia): RedirectResponse
    {
        $noticia->load('medio');
        $medio = $noticia->medio;

        $noticia->delete();
        $this->gestorMedios()->limpiar($medio);

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia eliminada correctamente.');
    }

    /**
     * Gestor de imágenes compartidas por el panel.
     */
    private function gestorMedios(): GestorMedios
    {
        return app(GestorMedios::class);
    }

    /**
     * Resuelve el id del estado de noticia a partir de su codigo.
     */
    private function estadoId(string $codigo): int
    {
        return (int) EstadoNoticia::where('codigo', $codigo)->value('id');
    }

    /**
     * Extrae y normaliza los campos generados por la IA presentes en la petición.
     *
     * Solo se incluyen los campos que llegan realmente, para no sobrescribir
     * valores existentes en peticiones parciales (por ejemplo, en los tests).
     *
     * @return array<string, mixed>
     */
    private function camposIa(Request $request): array
    {
        $datos = [];

        if ($request->exists('puntos_clave')) {
            $datos['puntos_clave'] = $this->aLista((string) $request->input('puntos_clave'), "\n");
        }

        if ($request->exists('etiquetas_ia')) {
            $datos['etiquetas_ia'] = array_slice($this->aLista((string) $request->input('etiquetas_ia'), ','), 0, 12);
        }

        return $datos;
    }

    /**
     * Convierte un texto separado por saltos de línea o comas en una lista limpia.
     *
     * @return array<int, string>
     */
    private function aLista(string $valor, string $separador): array
    {
        $partes = $separador === "\n"
            ? (preg_split('/\r\n|\r|\n/', $valor) ?: [])
            : explode($separador, $valor);

        $partes = array_filter(array_map('trim', $partes), fn (string $p) => $p !== '');

        return array_values(array_unique($partes));
    }

    /**
     * Generador de los campos de IA de la noticia.
     */
    private function generadorIa(): GeneradorIa
    {
        return app(GeneradorIa::class);
    }
}
