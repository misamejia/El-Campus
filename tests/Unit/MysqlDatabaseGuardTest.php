<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\TestCase as BaseTestCase;

class MysqlDatabaseGuardTest extends TestCase
{
    public function test_rechaza_mysql_si_la_base_no_termina_en_test(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("base configurada: 'campus_v2'");

        BaseTestCase::validarBaseDeDatosDePrueba('mysql', 'campus_v2');
    }

    public function test_acepta_mysql_si_la_base_termina_en_test(): void
    {
        BaseTestCase::validarBaseDeDatosDePrueba('mysql', 'campus_test');

        $this->addToAssertionCount(1);
    }

    public function test_rechaza_mariadb_si_la_base_no_termina_en_test(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("base configurada: 'campus_v2'");

        BaseTestCase::validarBaseDeDatosDePrueba('mariadb', 'campus_v2');
    }

    public function test_acepta_mariadb_si_la_base_termina_en_test(): void
    {
        BaseTestCase::validarBaseDeDatosDePrueba('mariadb', 'campus_test');

        $this->addToAssertionCount(1);
    }

    public function test_no_restringe_otros_motores(): void
    {
        BaseTestCase::validarBaseDeDatosDePrueba('sqlite', ':memory:');

        $this->addToAssertionCount(1);
    }
}
