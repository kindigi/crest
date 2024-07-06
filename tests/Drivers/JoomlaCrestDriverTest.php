<?php

use Crest\Drivers\Specific\JoomlaCrestDriver;

class JoomlaCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_joomla_projects()
    {
        $driver = new JoomlaCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('joomla'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_joomla_projects_with_public_directory()
    {
        $driver = new JoomlaCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new JoomlaCrestDriver();

        $projectPath = $this->projectDir('joomla');
        $this->assertEquals($projectPath.'/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
