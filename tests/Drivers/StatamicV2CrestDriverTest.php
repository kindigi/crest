<?php

use Crest\Drivers\Specific\StatamicV2Driver;

class StatamicV2CrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_statamic_projects()
    {
        $driver = new StatamicV2Driver();

        $this->assertTrue($driver->serves($this->projectDir('statamicv2'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_statamic_projects()
    {
        $driver = new StatamicV2Driver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new StatamicV2Driver();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/about/';

        $projectPath = $this->projectDir('statamicv2');
        $this->assertEquals($projectPath.'/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
