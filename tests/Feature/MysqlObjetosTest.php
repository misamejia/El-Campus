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

    private function limpiarDatosPropios(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('historial_estados_noticia')->truncate();
        DB::table('historial_estados_evento')->truncate();
        DB::table('noticias')->truncate();
        DB::table('eventos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
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

    public function test_trigger_ultimo_admin_unico_no_permite_degradar_ni_borrar(): void
    {
        $this->soloMysql();

        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        $lectorRoleId = DB::table('roles')->where('codigo', 'lector')->value('id');

        DB::table('users')->where('id', '>', 0)->delete();

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Unico',
            'email' => 'admin-unico@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Throwable::class);
        DB::table('users')->where('id', $adminId)->update(['role_id' => $lectorRoleId]);
    }

    public function test_trigger_ultimo_admin_borrar_unico_lanza_error(): void
    {
        $this->soloMysql();

        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');

        DB::table('users')->where('id', '>', 0)->delete();

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Unico 2',
            'email' => 'admin-unico2@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Throwable::class);
        DB::table('users')->where('id', $adminId)->delete();
    }

    public function test_trigger_ultimo_admin_con_dos_si_permite_degradar_uno(): void
    {
        $this->soloMysql();

        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        $lectorRoleId = DB::table('roles')->where('codigo', 'lector')->value('id');

        DB::table('users')->where('id', '>', 0)->delete();

        $adminId1 = DB::table('users')->insertGetId([
            'name' => 'Admin Uno',
            'email' => 'admin-uno@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insertGetId([
            'name' => 'Admin Dos',
            'email' => 'admin-dos@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $adminId1)->update(['role_id' => $lectorRoleId]);

        $this->assertEquals($lectorRoleId, DB::table('users')->where('id', $adminId1)->value('role_id'));
    }

    public function test_trigger_fecha_publicacion_insert_publicada_sin_fecha(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId([
            'nombre' => 'Test Cat FP',
            'slug' => 'test-cat-fp',
            'activa' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $autorId = DB::table('autores')->insertGetId([
            'nombre' => 'Test Autor FP',
            'slug' => 'test-autor-fp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test-user-mysql@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $estadoPublicado = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        $noticiaId = DB::table('noticias')->insertGetId([
            'titulo' => 'Noticia Publicada Sin Fecha',
            'slug' => 'noticia-publicada-sin-fecha',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'r',
            'contenido' => 'c',
            'fecha_publicacion' => null,
            'estado_id' => $estadoPublicado,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotNull(DB::table('noticias')->where('id', $noticiaId)->value('fecha_publicacion'));
    }

    public function test_trigger_fecha_publicacion_update_borrador_a_publicada_sin_fecha(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat FP2', 'slug' => 'cat-fp2', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor FP2', 'slug' => 'autor-fp2', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 2', 'email' => 'test-user2-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $estadoBorrador = DB::table('estados_noticia')->where('codigo', 'borrador')->value('id');
        $estadoPublicado = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        $noticiaId = DB::table('noticias')->insertGetId([
            'titulo' => 'Noticia Borrador',
            'slug' => 'noticia-borrador-fp',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'r',
            'contenido' => 'c',
            'fecha_publicacion' => null,
            'estado_id' => $estadoBorrador,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('noticias')->where('id', $noticiaId)->update([
            'estado_id' => $estadoPublicado,
            'fecha_publicacion' => null,
            'actualizado_por' => $usuarioId,
            'updated_at' => now(),
        ]);

        $this->assertNotNull(DB::table('noticias')->where('id', $noticiaId)->value('fecha_publicacion'));
    }

    public function test_trigger_historial_noticia_update_crea_fila_con_datos_correctos(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat HU', 'slug' => 'cat-hu', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor HU', 'slug' => 'autor-hu', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 3', 'email' => 'test-user3-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $estadoBorrador = DB::table('estados_noticia')->where('codigo', 'borrador')->value('id');
        $estadoPublicado = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        $noticiaId = DB::table('noticias')->insertGetId([
            'titulo' => 'Noticia Hist Update',
            'slug' => 'noticia-hist-update',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'r',
            'contenido' => 'c',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoBorrador,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filasAntes = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->count();

        DB::table('noticias')->where('id', $noticiaId)->update([
            'estado_id' => $estadoPublicado,
            'actualizado_por' => $usuarioId,
            'updated_at' => now(),
        ]);

        $filasDespues = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->count();
        $this->assertEquals($filasAntes + 1, $filasDespues);

        $ultima = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->orderByDesc('id')->first();
        $this->assertEquals($estadoBorrador, $ultima->estado_anterior_id);
        $this->assertEquals($estadoPublicado, $ultima->estado_nuevo_id);
        $this->assertEquals($usuarioId, $ultima->usuario_id);
    }

    public function test_trigger_historial_noticia_update_sin_cambio_no_crea_fila(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat HUSC', 'slug' => 'cat-husc', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor HUSC', 'slug' => 'autor-husc', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 4', 'email' => 'test-user4-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $estadoPublicado = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        $noticiaId = DB::table('noticias')->insertGetId([
            'titulo' => 'Noticia Sin Cambio',
            'slug' => 'noticia-sin-cambio',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'r',
            'contenido' => 'c',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoPublicado,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filasAntes = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->count();

        DB::table('noticias')->where('id', $noticiaId)->update([
            'titulo' => 'Noticia Sin Cambio Editado',
            'actualizado_por' => $usuarioId,
            'updated_at' => now(),
        ]);

        $filasDespues = DB::table('historial_estados_noticia')->where('noticia_id', $noticiaId)->count();
        $this->assertEquals($filasAntes, $filasDespues);
    }

    public function test_trigger_historial_evento_insert_y_update(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 5', 'email' => 'test-user5-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $estadoProgramado = DB::table('estados_evento')->where('codigo', 'programado')->value('id');
        $estadoRealizado = DB::table('estados_evento')->where('codigo', 'realizado')->value('id');
        $organizadorId = DB::table('organizadores')->insertGetId([
            'nombre' => 'Org Test HE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $eventoId = DB::table('eventos')->insertGetId([
            'titulo' => 'Evento Hist',
            'slug' => 'evento-hist',
            'descripcion' => 'd',
            'lugar' => 'l',
            'fecha_inicio' => now()->addDays(1),
            'organizador_id' => $organizadorId,
            'estado_id' => $estadoProgramado,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertGreaterThanOrEqual(1, DB::table('historial_estados_evento')->where('evento_id', $eventoId)->count());

        $filasAntes = DB::table('historial_estados_evento')->where('evento_id', $eventoId)->count();
        DB::table('eventos')->where('id', $eventoId)->update([
            'estado_id' => $estadoRealizado,
            'actualizado_por' => $usuarioId,
            'updated_at' => now(),
        ]);
        $filasDespues = DB::table('historial_estados_evento')->where('evento_id', $eventoId)->count();
        $this->assertEquals($filasAntes + 1, $filasDespues);
    }

    public function test_check_slug_invalido_noticias_eventos_categorias_autores(): void
    {
        $this->soloMysql();

        $this->expectException(\Throwable::class);
        DB::table('noticias')->insert(['titulo' => 'x', 'slug' => 'Slug_Invalido', 'categoria_id' => 1, 'autor_id' => 1, 'resumen' => 'r', 'contenido' => 'c', 'estado_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_check_titulo_vacio_lanza_error(): void
    {
        $this->soloMysql();
        $this->expectException(\Throwable::class);
        DB::table('noticias')->insert(['titulo' => '   ', 'slug' => 'titulo-vacio-test', 'categoria_id' => 1, 'autor_id' => 1, 'resumen' => 'r', 'contenido' => 'c', 'estado_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_check_email_invalido_users_lanza_error(): void
    {
        $this->soloMysql();
        $this->expectException(\Throwable::class);
        DB::table('users')->insert(['name' => 'x', 'email' => 'sin-arroba', 'password' => 'x', 'role_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_check_medios_peso_y_mime(): void
    {
        $this->soloMysql();
        $this->expectException(\Throwable::class);
        DB::table('medios')->insert(['disco' => 'publico', 'ruta' => 'imagenes/test.jpg', 'tipo_mime' => 'image/jpeg', 'tamano_bytes' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_check_medios_peso_superior_lanza_error(): void
    {
        $this->soloMysql();
        $this->expectException(\Throwable::class);
        DB::table('medios')->insert(['disco' => 'publico', 'ruta' => 'imagenes/test2.jpg', 'tipo_mime' => 'image/jpeg', 'tamano_bytes' => 3000000, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_check_medios_mime_no_permitido_lanza_error(): void
    {
        $this->soloMysql();
        $this->expectException(\Throwable::class);
        DB::table('medios')->insert(['disco' => 'publico', 'ruta' => 'imagenes/test3.gif', 'tipo_mime' => 'image/gif', 'tamano_bytes' => 1000, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_vista_v_noticias_publicas_solo_publicadas_con_categoria_activa(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaActiva = DB::table('categorias')->insertGetId(['nombre' => 'Cat Activa VP', 'slug' => 'cat-activa-vp', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $categoriaInactiva = DB::table('categorias')->insertGetId(['nombre' => 'Cat Inactiva VP', 'slug' => 'cat-inactiva-vp', 'activa' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor VP', 'slug' => 'autor-vp', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 6', 'email' => 'test-user6-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $pub = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');
        $bor = DB::table('estados_noticia')->where('codigo', 'borrador')->value('id');

        DB::table('noticias')->insert([
            'titulo' => 'Pub Activa', 'slug' => 'pub-activa-vp', 'categoria_id' => $categoriaActiva, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $pub, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('noticias')->insert([
            'titulo' => 'Borr Activa', 'slug' => 'borr-activa-vp', 'categoria_id' => $categoriaActiva, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $bor, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('noticias')->insert([
            'titulo' => 'Pub Inactiva', 'slug' => 'pub-inactiva-vp', 'categoria_id' => $categoriaInactiva, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $pub, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $slugs = DB::select('SELECT slug FROM v_noticias_publicas');
        $slugsArr = array_map(fn ($s) => $s->slug, $slugs);
        $this->assertContains('pub-activa-vp', $slugsArr);
        $this->assertNotContains('borr-activa-vp', $slugsArr);
        $this->assertNotContains('pub-inactiva-vp', $slugsArr);
    }

    public function test_vista_v_noticias_por_estado_conteos_correctos(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat VNE', 'slug' => 'cat-vne', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor VNE', 'slug' => 'autor-vne', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 7', 'email' => 'test-user7-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $pub = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');
        $bor = DB::table('estados_noticia')->where('codigo', 'borrador')->value('id');

        DB::table('noticias')->insert(['titulo' => 'N1', 'slug' => 'n1-vne', 'categoria_id' => $categoriaId, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $pub, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('noticias')->insert(['titulo' => 'N2', 'slug' => 'n2-vne', 'categoria_id' => $categoriaId, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $bor, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);

        $res = DB::select('SELECT codigo, total FROM v_noticias_por_estado');
        $map = [];
        foreach ($res as $r) {
            $map[$r->codigo] = (int) $r->total;
        }
        $this->assertArrayHasKey('publicada', $map);
        $this->assertArrayHasKey('borrador', $map);
    }

    public function test_vista_v_eventos_por_estado_conteos_correctos(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 8', 'email' => 'test-user8-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $prog = DB::table('estados_evento')->where('codigo', 'programado')->value('id');
        $real = DB::table('estados_evento')->where('codigo', 'realizado')->value('id');
        $orgId = DB::table('organizadores')->insertGetId(['nombre' => 'Org VEP', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('eventos')->insert(['titulo' => 'E1', 'slug' => 'e1-vep', 'descripcion' => 'd', 'lugar' => 'l', 'fecha_inicio' => now()->addDays(1), 'organizador_id' => $orgId, 'estado_id' => $prog, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('eventos')->insert(['titulo' => 'E2', 'slug' => 'e2-vep', 'descripcion' => 'd', 'lugar' => 'l', 'fecha_inicio' => now()->subDays(1), 'organizador_id' => $orgId, 'estado_id' => $real, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);

        $res = DB::select('SELECT codigo, total FROM v_eventos_por_estado');
        $map = [];
        foreach ($res as $r) {
            $map[$r->codigo] = (int) $r->total;
        }
        $this->assertArrayHasKey('programado', $map);
        $this->assertArrayHasKey('realizado', $map);
    }

    public function test_vista_v_dashboard_resumen_totales_correctos(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat VDR', 'slug' => 'cat-vdr', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor VDR', 'slug' => 'autor-vdr', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 9', 'email' => 'test-user9-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $pub = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');
        $prog = DB::table('estados_evento')->where('codigo', 'programado')->value('id');
        $orgId = DB::table('organizadores')->insertGetId(['nombre' => 'Org VDR', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('noticias')->insert(['titulo' => 'NDR', 'slug' => 'ndr-vdr', 'categoria_id' => $categoriaId, 'autor_id' => $autorId, 'resumen' => 'r', 'contenido' => 'c', 'fecha_publicacion' => now(), 'estado_id' => $pub, 'destacada' => 0, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('eventos')->insert(['titulo' => 'EDR', 'slug' => 'edr-vdr', 'descripcion' => 'd', 'lugar' => 'l', 'fecha_inicio' => now()->addDays(1), 'organizador_id' => $orgId, 'estado_id' => $prog, 'creado_por' => $usuarioId, 'actualizado_por' => $usuarioId, 'created_at' => now(), 'updated_at' => now()]);

        $res = DB::select('SELECT total_noticias, total_eventos, total_autores, total_categorias, total_usuarios FROM v_dashboard_resumen');
        $this->assertGreaterThanOrEqual(1, (int) $res[0]->total_noticias);
        $this->assertGreaterThanOrEqual(1, (int) $res[0]->total_eventos);
    }

    public function test_fulltext_match_against_devuelve_noticia_esperada(): void
    {
        $this->soloMysql();
        $this->limpiarDatosPropios();

        $categoriaId = DB::table('categorias')->insertGetId(['nombre' => 'Cat FT', 'slug' => 'cat-ft', 'activa' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $autorId = DB::table('autores')->insertGetId(['nombre' => 'Autor FT', 'slug' => 'autor-ft', 'created_at' => now(), 'updated_at' => now()]);
        $usuario = DB::table('users')->first();
        $usuarioId = $usuario ? $usuario->id : DB::table('users')->insertGetId(['name' => 'Test User 10', 'email' => 'test-user10-mysql@example.com', 'email_verified_at' => now(), 'password' => bcrypt('password'), 'role_id' => DB::table('roles')->where('codigo', 'lector')->value('id') ?? 1, 'created_at' => now(), 'updated_at' => now()]);
        $pub = DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');

        DB::table('noticias')->insert([
            'titulo' => 'Zebulon Quantum',
            'slug' => 'zebulon-quantum-ft',
            'categoria_id' => $categoriaId,
            'autor_id' => $autorId,
            'resumen' => 'resumen unico',
            'contenido' => 'contenido especial',
            'fecha_publicacion' => now(),
            'estado_id' => $pub,
            'destacada' => 0,
            'creado_por' => $usuarioId,
            'actualizado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = DB::select('SELECT slug FROM noticias WHERE MATCH(titulo,resumen,contenido) AGAINST(? IN NATURAL LANGUAGE MODE)', ['Zebulon']);
        $slugs = array_map(fn ($r) => $r->slug, $res);
        $this->assertContains('zebulon-quantum-ft', $slugs);
    }
}
