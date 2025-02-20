<?php

use Crest\Drivers\Specific\CakeCrestDriver;

class CakeCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_cake_projects()
    {
        $driver = new CakeCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('cake'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_cake_projects()
    {
        $driver = new CakeCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new CakeCrestDriver();

        $projectPath = $this->projectDir('cake');
        $this->assertEquals($projectPath.'/webroot/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
