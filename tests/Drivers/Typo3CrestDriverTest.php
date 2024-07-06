<?php

use Crest\Drivers\Specific\Typo3Driver;

class Typo3CrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_typo3_projects()
    {
        $driver = new Typo3Driver();

        $this->assertTrue($driver->serves($this->projectDir('typo3'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_typo3_projects()
    {
        $driver = new Typo3Driver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new Typo3Driver();

        $projectPath = $this->projectDir('typo3');
        $this->assertEquals($projectPath.'/web/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
