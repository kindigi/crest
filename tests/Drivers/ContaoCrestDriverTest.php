<?php

use Crest\Drivers\Specific\ContaoCrestDriver;

class ContaoCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_contao_projects()
    {
        $driver = new ContaoCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('contao'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_contao_projects()
    {
        $driver = new ContaoCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new ContaoCrestDriver();

        $projectPath = $this->projectDir('contao');
        $this->assertEquals($projectPath.'/web/app.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
