<?php

use Crest\Drivers\Specific\StatamicV1Driver;

class StatamicV1CrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_statamicv1_projects()
    {
        $driver = new StatamicV1Driver();

        $this->assertTrue($driver->serves($this->projectDir('statamicv1'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_statamicv1_projects()
    {
        $driver = new StatamicV1Driver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new StatamicV1Driver();

        $projectPath = $this->projectDir('statamicv1');
        $this->assertEquals($projectPath.'/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
