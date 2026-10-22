<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Objetos de base de datos específicos de MySQL/MariaDB: restricciones
     * CHECK, índices FULLTEXT, triggers de negocio y vistas de apoyo.
     *
     * Se omite por completo en otros motores (por ejemplo SQLite, usado en
     * las pruebas automatizadas), donde estos objetos no son portables.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->checks();
        $this->fulltext();
        $this->triggers();
        $this->views();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ([
            'v_noticias_publicas',
            'v_eventos_publicos',
            'v_dashboard_resumen',
            'v_noticias_por_estado',
            'v_eventos_por_estado',
        ] as $vista) {
            DB::statement("DROP VIEW IF EXISTS `{$vista}`");
        }

        foreach ([
            'trg_eventos_ai_historial',
            'trg_eventos_au_historial',
            'trg_noticias_bi_fecha_publicacion',
            'trg_noticias_ai_historial',
            'trg_noticias_bu_fecha_publicacion',
            'trg_noticias_au_historial',
            'trg_users_bu_ultimo_admin',
            'trg_users_bd_ultimo_admin',
        ] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS `{$trigger}`");
        }
    }

    private function checks(): void
    {
        $reglas = [
            ['roles', 'ck_roles_codigo', "codigo regexp '^[a-z_]+$'"],
            ['estados_noticia', 'ck_estados_noticia_codigo', "codigo regexp '^[a-z_]+$'"],
            ['estados_evento', 'ck_estados_evento_codigo', "codigo regexp '^[a-z_]+$'"],
            ['categorias', 'ck_categorias_slug', "slug regexp '^[a-z0-9]+(-[a-z0-9]+)*$'"],
            ['autores', 'ck_autores_slug', "slug regexp '^[a-z0-9]+(-[a-z0-9]+)*$'"],
            ['autores', 'ck_autores_email', "email is null or email like '%_@_%._%'"],
            ['users', 'ck_users_email', "email like '%_@_%._%'"],
            ['medios', 'ck_medios_mime', "tipo_mime in ('image/jpeg','image/png','image/webp','image/avif','image/svg+xml')"],
            ['medios', 'ck_medios_peso', 'tamano_bytes between 1 and 2097152'],
            ['noticias', 'ck_noticias_slug', "slug regexp '^[a-z0-9]+(-[a-z0-9]+)*$'"],
            ['noticias', 'ck_noticias_titulo', 'char_length(trim(titulo)) > 0'],
            ['eventos', 'ck_eventos_slug', "slug regexp '^[a-z0-9]+(-[a-z0-9]+)*$'"],
            ['eventos', 'ck_eventos_titulo', 'char_length(trim(titulo)) > 0'],
            ['eventos', 'ck_eventos_fechas', 'fecha_fin is null or fecha_fin >= fecha_inicio'],
        ];

        foreach ($reglas as [$tabla, $nombre, $condicion]) {
            DB::statement("ALTER TABLE `{$tabla}` ADD CONSTRAINT `{$nombre}` CHECK ({$condicion})");
        }
    }

    private function fulltext(): void
    {
        DB::statement('ALTER TABLE `noticias` ADD FULLTEXT `ft_noticias_busqueda` (`titulo`, `resumen`, `contenido`)');
        DB::statement('ALTER TABLE `eventos` ADD FULLTEXT `ft_eventos_busqueda` (`titulo`, `descripcion`, `lugar`)');
    }

    private function triggers(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_noticias_bi_fecha_publicacion` BEFORE INSERT ON `noticias`
            FOR EACH ROW
            BEGIN
                IF NEW.fecha_publicacion IS NULL
                   AND NEW.estado_id = (SELECT id FROM estados_noticia WHERE codigo = 'publicada') THEN
                    SET NEW.fecha_publicacion = NOW();
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_noticias_bu_fecha_publicacion` BEFORE UPDATE ON `noticias`
            FOR EACH ROW
            BEGIN
                IF NEW.fecha_publicacion IS NULL
                   AND NEW.estado_id = (SELECT id FROM estados_noticia WHERE codigo = 'publicada') THEN
                    SET NEW.fecha_publicacion = NOW();
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_noticias_ai_historial` AFTER INSERT ON `noticias`
            FOR EACH ROW
            BEGIN
                INSERT INTO historial_estados_noticia (noticia_id, estado_anterior_id, estado_nuevo_id, usuario_id)
                VALUES (NEW.id, NULL, NEW.estado_id, NEW.creado_por);
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_noticias_au_historial` AFTER UPDATE ON `noticias`
            FOR EACH ROW
            BEGIN
                IF OLD.estado_id <> NEW.estado_id THEN
                    INSERT INTO historial_estados_noticia (noticia_id, estado_anterior_id, estado_nuevo_id, usuario_id)
                    VALUES (NEW.id, OLD.estado_id, NEW.estado_id, NEW.actualizado_por);
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_eventos_ai_historial` AFTER INSERT ON `eventos`
            FOR EACH ROW
            BEGIN
                INSERT INTO historial_estados_evento (evento_id, estado_anterior_id, estado_nuevo_id, usuario_id)
                VALUES (NEW.id, NULL, NEW.estado_id, NEW.creado_por);
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_eventos_au_historial` AFTER UPDATE ON `eventos`
            FOR EACH ROW
            BEGIN
                IF OLD.estado_id <> NEW.estado_id THEN
                    INSERT INTO historial_estados_evento (evento_id, estado_anterior_id, estado_nuevo_id, usuario_id)
                    VALUES (NEW.id, OLD.estado_id, NEW.estado_id, NEW.actualizado_por);
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_users_bu_ultimo_admin` BEFORE UPDATE ON `users`
            FOR EACH ROW
            BEGIN
                DECLARE v_admin TINYINT UNSIGNED;
                SELECT id INTO v_admin FROM roles WHERE codigo = 'administrador' LIMIT 1;
                IF OLD.role_id = v_admin AND NEW.role_id <> v_admin
                   AND (SELECT COUNT(*) FROM users WHERE role_id = v_admin) <= 1 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'No se puede dejar el sistema sin administradores.';
                END IF;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `trg_users_bd_ultimo_admin` BEFORE DELETE ON `users`
            FOR EACH ROW
            BEGIN
                DECLARE v_admin TINYINT UNSIGNED;
                SELECT id INTO v_admin FROM roles WHERE codigo = 'administrador' LIMIT 1;
                IF OLD.role_id = v_admin
                   AND (SELECT COUNT(*) FROM users WHERE role_id = v_admin) <= 1 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'No se puede eliminar al único administrador del sistema.';
                END IF;
            END
        SQL);
    }

    private function views(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW `v_noticias_publicas` AS
            SELECT n.id, n.slug, n.titulo, n.resumen, n.contenido, n.destacada,
                   n.fecha_publicacion,
                   c.id AS categoria_id, c.nombre AS categoria, c.slug AS categoria_slug,
                   a.id AS autor_id, a.nombre AS autor, a.slug AS autor_slug,
                   m.disco AS imagen_disco, m.ruta AS imagen_ruta, m.texto_alternativo AS imagen_alt
            FROM noticias n
            JOIN estados_noticia e ON e.id = n.estado_id AND e.es_publico = 1
            JOIN categorias c ON c.id = n.categoria_id AND c.activa = 1
            JOIN autores a ON a.id = n.autor_id
            LEFT JOIN medios m ON m.id = n.imagen_id
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW `v_eventos_publicos` AS
            SELECT ev.id, ev.slug, ev.titulo, ev.descripcion, ev.lugar,
                   ev.fecha_inicio, ev.fecha_fin,
                   es.codigo AS estado, o.nombre AS organizador,
                   m.disco AS imagen_disco, m.ruta AS imagen_ruta, m.texto_alternativo AS imagen_alt
            FROM eventos ev
            JOIN estados_evento es ON es.id = ev.estado_id AND es.es_publico = 1
            LEFT JOIN organizadores o ON o.id = ev.organizador_id
            LEFT JOIN medios m ON m.id = ev.imagen_id
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW `v_dashboard_resumen` AS
            SELECT
                (SELECT COUNT(*) FROM noticias) AS total_noticias,
                (SELECT COUNT(*) FROM eventos) AS total_eventos,
                (SELECT COUNT(*) FROM autores) AS total_autores,
                (SELECT COUNT(*) FROM categorias) AS total_categorias,
                (SELECT COUNT(*) FROM users) AS total_usuarios
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW `v_noticias_por_estado` AS
            SELECT e.id AS estado_id, e.codigo, e.nombre, COUNT(n.id) AS total
            FROM estados_noticia e
            LEFT JOIN noticias n ON n.estado_id = e.id
            GROUP BY e.id, e.codigo, e.nombre
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW `v_eventos_por_estado` AS
            SELECT e.id AS estado_id, e.codigo, e.nombre, COUNT(ev.id) AS total
            FROM estados_evento e
            LEFT JOIN eventos ev ON ev.estado_id = e.id
            GROUP BY e.id, e.codigo, e.nombre
        SQL);
    }
};
