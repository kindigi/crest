<?php

use Crest\Drivers\Specific\SymfonyDriver;

class SymfonyCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_symfony_projects()
    {
        $driver = new SymfonyDriver();

        $this->assertTrue($driver->serves($this->projectDir('symfony'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_symfony_projects()
    {
        $driver = new SymfonyDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new SymfonyDriver();

        $projectPath = $this->projectDir('symfony');
        $this->assertEquals($projectPath.'/web/app.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
