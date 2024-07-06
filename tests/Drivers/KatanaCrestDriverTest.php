<?php

use Crest\Drivers\Specific\KatanaDriver;

class KatanaCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_katana_projects()
    {
        $driver = new KatanaDriver();

        $this->assertTrue($driver->serves($this->projectDir('katana'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_katana_projects_with_public_directory()
    {
        $driver = new KatanaDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_mutates_uri()
    {
        $driver = new KatanaDriver();

        $this->assertEquals('/public/about', $driver->mutateUri('/about'));
    }
}
