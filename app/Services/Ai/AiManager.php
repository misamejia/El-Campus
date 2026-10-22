<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiGateway;
use App\Services\Ai\Gateways\FakeGateway;
use App\Services\Ai\Gateways\GeminiGateway;

/**
 * Punto de entrada único a la IA. Selecciona el proveedor según la
 * configuración y delega en él todas las operaciones.
 */
class AiManager implements AiGateway
{
    private ?AiGateway $gateway = null;

    public function gateway(): AiGateway
    {
        return $this->gateway ??= $this->resolver();
    }

    /**
     * ¿Estamos usando un proveedor real (Gemini) o el driver de demo?
     */
    public function esReal(): bool
    {
        return $this->gateway() instanceof GeminiGateway;
    }

    public function nombre(): string
    {
        return $this->esReal() ? 'Gemini' : 'demo';
    }

    private function resolver(): AiGateway
    {
        $driver = (string) config('ai.driver', 'auto');
        $conApiKey = filled(config('prism.providers.gemini.api_key'));

        return match (true) {
            $driver === 'fake' => new FakeGateway,
            $driver === 'gemini' => new GeminiGateway,
            $conApiKey => new GeminiGateway,
            default => new FakeGateway,
        };
    }

    public function resumir(string $titulo, string $contenido): array
    {
        return $this->gateway()->resumir($titulo, $contenido);
    }

    public function titulares(string $titulo, string $contenido, int $cantidad = 5): array
    {
        return $this->gateway()->titulares($titulo, $contenido, $cantidad);
    }

    public function etiquetas(string $titulo, string $contenido, int $cantidad = 6): array
    {
        return $this->gateway()->etiquetas($titulo, $contenido, $cantidad);
    }

    public function revisarEstilo(string $titulo, string $contenido): array
    {
        return $this->gateway()->revisarEstilo($titulo, $contenido);
    }

    public function embedding(string $texto): array
    {
        return $this->gateway()->embedding($texto);
    }
}
