<?php

namespace Tests\Feature;

use App\Http\Responses\LoginResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginResponseComportamientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_lector_ignora_intended_del_panel_y_va_a_inicio(): void
    {
        $lector = User::factory()->lector()->create(['email_verified_at' => now()]);

        Session::put('url.intended', route('dashboard'));

        $response = $this->actingAs($lector)->get(route('dashboard'));

        $response->assertStatus(403);

        $respuesta = app(LoginResponse::class)->toResponse(
            request()->setUserResolver(fn () => $lector)
        );

        $this->assertStringContainsString(route('inicio'), $respuesta->getTargetUrl());
    }

    public function test_editor_usa_intended_hacia_dashboard(): void
    {
        $editor = User::factory()->editor()->create(['email_verified_at' => now()]);

        Session::put('url.intended', route('dashboard'));

        $respuesta = app(LoginResponse::class)->toResponse(
            request()->setUserResolver(fn () => $editor)
        );

        $this->assertStringContainsString(route('dashboard'), $respuesta->getTargetUrl());
    }

    public function test_administrador_usa_intended_hacia_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        Session::put('url.intended', route('dashboard'));

        $respuesta = app(LoginResponse::class)->toResponse(
            request()->setUserResolver(fn () => $admin)
        );

        $this->assertStringContainsString(route('dashboard'), $respuesta->getTargetUrl());
    }
}
