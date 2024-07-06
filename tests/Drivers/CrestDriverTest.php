<?php

use Crest\Drivers\BasicCrestDriver;
use Crest\Drivers\Specific\BedrockCrestDriver;
use Crest\Drivers\CrestDriver;

class CrestDriverTest extends BaseDriverTestCase
{
    public function test_it_gets_drivers_in_given_path()
    {
        $output = CrestDriver::driversIn(__DIR__.'/../files/Drivers');

        $this->assertEquals(2, count($output));
        $this->assertContains('Test1CrestDriver', $output);
        $this->assertContains('Test2CrestDriver', $output);
    }

    public function test_it_assigns_drivers_to_given_project()
    {
        $assignedDriver = CrestDriver::assign($this->projectDir('bedrock'), 'my-site', '/');

        $this->assertEquals(BedrockCrestDriver::class, get_class($assignedDriver));
    }

    public function test_it_prioritizes_non_basic_matches()
    {
        $assignedDriver = CrestDriver::assign($this->projectDir('laravel'), 'my-site', '/');

        $this->assertNotEquals('Crest\Drivers\BasicWithPublicCrestDriver', get_class($assignedDriver));
        $this->assertNotEquals('Crest\Drivers\BasicCrestDriver', get_class($assignedDriver));
    }

    public function test_it_prioritizes_statamic()
    {
        $assignedDriver = CrestDriver::assign($this->projectDir('statamic'), 'my-site', '/');
        $this->assertEquals('Crest\Drivers\Specific\StatamicDriver', get_class($assignedDriver));

        $assignedDriver = CrestDriver::assign($this->projectDir('laravel'), 'my-site', '/');
        $this->assertEquals('Crest\Drivers\LaravelCrestDriver', get_class($assignedDriver));
    }

    public function test_it_checks_composer_dependencies()
    {
        $driver = new BasicCrestDriver;
        $this->assertTrue($driver->composerRequires(__DIR__.'/../files/sites/has-composer', 'tightenco/collect'));
        $this->assertFalse($driver->composerRequires(__DIR__.'/../files/sites/has-composer', 'tightenco/ziggy'));
    }
}
