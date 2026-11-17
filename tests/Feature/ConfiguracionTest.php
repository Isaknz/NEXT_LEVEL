<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfiguracionTest extends TestCase
{
    public function test_la_zona_horaria_por_defecto_es_la_de_peru(): void
    {
        $this->assertSame('America/Lima', config('app.timezone'));
    }
}
