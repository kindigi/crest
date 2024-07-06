<?php

use Crest\Drivers\Specific\JigsawCrestDriver;

class JigsawCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_jigsaw_projects()
    {
        $driver = new JigsawCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('jigsaw'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_jigsaw_projects_with_public_directory()
    {
        $driver = new JigsawCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_mutates_uri()
    {
        $driver = new JigsawCrestDriver();

        $this->assertEquals('/build_local/about', $driver->mutateUri('/about'));
    }
}
