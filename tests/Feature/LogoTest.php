<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_logo_apunta_a_un_archivo_que_existe(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        preg_match('#<img[^>]+src="([^"]+)"[^>]*alt="Logo de Next Level School"#i', $html, $m);

        $this->assertNotEmpty($m, 'el login deberia renderizar el logo');

        $ruta = parse_url($m[1], PHP_URL_PATH);
        $archivo = public_path(ltrim($ruta, '/'));

        $this->assertFileExists(
            $archivo,
            "el src del logo apunta a {$ruta} y ese archivo no existe en public/"
        );

        $bytes = filesize($archivo);
        $this->assertGreaterThan(0, $bytes, 'el logo no puede estar vacio');
    }
}
