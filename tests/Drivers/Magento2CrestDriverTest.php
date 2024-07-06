<?php

use Crest\Drivers\Specific\Magento2Driver;

class Magento2CrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_magento2_projects()
    {
        $driver = new Magento2Driver();

        $this->assertTrue($driver->serves($this->projectDir('magento2'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_magento2_projects_with_public_directory()
    {
        $driver = new Magento2Driver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new Magento2Driver();

        $projectPath = $this->projectDir('magento2');
        $this->assertEquals($projectPath.'/pub/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
