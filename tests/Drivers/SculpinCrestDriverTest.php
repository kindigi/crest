<?php

use Crest\Drivers\Specific\SculpinDriver;

class SculpinCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_sculpin_projects()
    {
        $driver = new SculpinDriver();

        $this->assertTrue($driver->serves($this->projectDir('sculpin'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_sculpin_projects_with_public_directory()
    {
        $driver = new SculpinDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_mutates_uri()
    {
        $driver = new SculpinDriver();

        $this->assertEquals('/output_dev/about', $driver->mutateUri('/about'));
    }
}
