<?php

namespace App\Livewire\Noticias;

use App\Services\Ai\AiManager;
use Livewire\Component;
use Throwable;

/**
 * Asistente de redacción con IA embebido en el formulario de noticias.
 * No envía el formulario: solo rellena los campos del editor y muestra
 * sugerencias. Las acciones son síncronas y nunca lanzan errores al usuario.
 */
class AsistenteIa extends Component
{
    /** ¿Está configurado un proveedor real (Gemini) o es modo demo? */
    public bool $real = true;

    public ?string $error = null;

    public ?string $resumen = null;

    /** @var array<int, string> */
    public array $puntos = [];

    /** @var array<int, string> */
    public array $titulares = [];

    /** @var array<int, string> */
    public array $etiquetas = [];

    public ?string $estiloResumen = null;

    /** @var array<int, array{tipo: string, mensaje: string}> */
    public array $estilo = [];

    public function mount(): void
    {
        $this->real = app(AiManager::class)->esReal();
    }

    public function generarResumen(string $titulo, string $contenido): void
    {
        $this->ejecutar(function (AiManager $ai) use ($titulo, $contenido) {
            $resultado = $ai->resumir($titulo, $contenido !== '' ? $contenido : $titulo);
            $this->resumen = $resultado['resumen'];
            $this->puntos = $resultado['puntos_clave'];
            $this->dispatch('ia-resumen', resumen: $this->resumen, puntos: $this->puntos);
        });
    }

    public function sugerirTitulares(string $titulo, string $contenido): void
    {
        $this->ejecutar(function (AiManager $ai) use ($titulo, $contenido) {
            $this->titulares = $ai->titulares($titulo, $contenido);
        });
    }

    public function sugerirEtiquetas(string $titulo, string $contenido): void
    {
        $this->ejecutar(function (AiManager $ai) use ($titulo, $contenido) {
            $this->etiquetas = $ai->etiquetas($titulo, $contenido);
        });
    }

    public function aplicarEtiquetas(): void
    {
        $this->dispatch('ia-etiquetas', etiquetas: $this->etiquetas);
    }

    public function revisarEstilo(string $titulo, string $contenido): void
    {
        $this->ejecutar(function (AiManager $ai) use ($titulo, $contenido) {
            $revision = $ai->revisarEstilo($titulo, $contenido);
            $this->estiloResumen = $revision['resumen'];
            $this->estilo = $revision['sugerencias'];
        });
    }

    public function usarTitular(string $titulo): void
    {
        $this->dispatch('ia-titulo', titulo: $titulo);
    }

    public function render()
    {
        return view('livewire.noticias.asistente-ia');
    }

    /**
     * Ejecuta una operación de IA capturando cualquier fallo.
     */
    private function ejecutar(callable $operacion): void
    {
        $this->error = null;

        try {
            $operacion(app(AiManager::class));
        } catch (Throwable $e) {
            report($e);
            $this->error = 'No se pudo completar la operación con IA. Inténtalo de nuevo.';
        }
    }
}
