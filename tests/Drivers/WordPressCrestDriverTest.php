<?php

use Crest\Drivers\Specific\WordPressCrestDriver;

class WordPressCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_wordpress_projects()
    {
        $driver = new WordPressCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('wordpress'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_wordpress_projects()
    {
        $driver = new WordPressCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new WordPressCrestDriver();

        $projectPath = $this->projectDir('wordpress');
        $this->assertEquals($projectPath.'/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
