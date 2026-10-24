<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MysqlObjetosTest extends TestCase
{
    use RefreshDatabase;

    private function soloMysql(): void
    {
        if (! in_array((string) DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Solo aplicable a MySQL/MariaDB.');
        }
    }

    public function test_triggers_de_historial_y_validacion_existen(): void
    {
        $this->soloMysql();

        $triggers = [
            'trg_noticias_bi_fecha_publicacion',
            'trg_noticias_bu_fecha_publicacion',
            'trg_noticias_ai_historial',
            'trg_noticias_au_historial',
            'trg_eventos_ai_historial',
            'trg_eventos_au_historial',
            'trg_users_bu_ultimo_admin',
            'trg_users_bd_ultimo_admin',
        ];

        $existentes = DB::select('SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()');
        $nombres = array_map(fn ($t) => $t->TRIGGER_NAME, $existentes);

        foreach ($triggers as $trigger) {
            $this->assertContains($trigger, $nombres, "Falta el trigger {$trigger}");
        }
    }

    public function test_checks_de_dominio_existen(): void
    {
        $this->soloMysql();

        $esperados = [
            ['roles', 'ck_roles_codigo'],
            ['estados_noticia', 'ck_estados_noticia_codigo'],
            ['estados_evento', 'ck_estados_evento_codigo'],
            ['categorias', 'ck_categorias_slug'],
            ['autores', 'ck_autores_slug'],
            ['autores', 'ck_autores_email'],
            ['users', 'ck_users_email'],
            ['medios', 'ck_medios_mime'],
            ['medios', 'ck_medios_peso'],
            ['noticias', 'ck_noticias_slug'],
            ['noticias', 'ck_noticias_titulo'],
            ['eventos', 'ck_eventos_slug'],
            ['eventos', 'ck_eventos_titulo'],
            ['eventos', 'ck_eventos_fechas'],
        ];

        $checks = DB::select("SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'CHECK'");
        $pares = array_map(fn ($c) => $c->TABLE_NAME.'.'.$c->CONSTRAINT_NAME, $checks);

        foreach ($esperados as [$tabla, $nombre]) {
            $this->assertContains($tabla.'.'.$nombre, $pares, "Falta el CHECK {$nombre} en {$tabla}");
        }

        $this->assertCount(14, $esperados);
    }

    public function test_vistas_creadas_existen(): void
    {
        $this->soloMysql();

        $vistas = [
            'v_noticias_publicas',
            'v_eventos_publicos',
            'v_dashboard_resumen',
            'v_noticias_por_estado',
            'v_eventos_por_estado',
        ];

        $existentes = DB::select('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = DATABASE()');
        $nombres = array_map(fn ($v) => $v->TABLE_NAME, $existentes);

        foreach ($vistas as $vista) {
            $this->assertContains($vista, $nombres, "Falta la vista {$vista}");
        }
    }

    public function test_fulltext_en_noticias_y_eventos_existen(): void
    {
        $this->soloMysql();

        $indices = DB::select("SHOW INDEX FROM noticias WHERE Index_type = 'FULLTEXT'");
        $nombresNoticias = array_map(fn ($i) => $i->Key_name, $indices);
        $this->assertContains('ft_noticias_busqueda', $nombresNoticias);

        $indicesEvento = DB::select("SHOW INDEX FROM eventos WHERE Index_type = 'FULLTEXT'");
        $nombresEventos = array_map(fn ($i) => $i->Key_name, $indicesEvento);
        $this->assertContains('ft_eventos_busqueda', $nombresEventos);
    }

    public function test_trg_noticias_ai_historial_crea_registro_al_insertar(): void
    {
        $this->soloMysql();

        $categoriaId = DB::table('categorias')->value('id') ?? DB::table('categorias')->insertGetId([
            'nombre' => 'Test Cat',
            'slug' => 'test-cat',
            'activa' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $autorId = DB::table('autores')->value('id') ?? DB::table('autores')->insertGetId([
            'nombre' => 'Test Autor',
            'slug' => 'test-autor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usuarioId = DB::table('users')->value('id') ?? DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test-trigger@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $estadoId = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        DB::table('noticias')->insert([
            'titulo' => 'Noticia Test Trigger',
            'slug' => 'noticia-test-trigger',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'r',
            'contenido' => 'c',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoId,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $noticiaId = DB::table('noticias')->where('slug', 'noticia-test-trigger')->value('id');
        $historial = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->count();

        $this->assertGreaterThanOrEqual(1, $historial);
    }

    public function test_ck_eventos_fechas_rechaza_fecha_fin_anterior(): void
    {
        $this->soloMysql();

        $estadoId = DB::table('estados_evento')->where('codigo', 'programado')->value('id');
        $this->expectException(\Throwable::class);

        DB::table('eventos')->insert([
            'titulo' => 'Evento Fechas',
            'slug' => 'evento-fechas-test',
            'descripcion' => 'd',
            'lugar' => 'l',
            'fecha_inicio' => now()->addDays(2),
            'fecha_fin' => now()->addDays(1),
            'estado_id' => $estadoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
