<?php

use Crest\Drivers\Specific\BedrockCrestDriver;

class BedrockCrestDriverTest extends BaseDriverTestCase
{
    public function test_it_serves_bedrock_projects()
    {
        $driver = new BedrockCrestDriver();

        $this->assertTrue($driver->serves($this->projectDir('bedrock'), 'my-site', '/'));
    }

    public function test_it_doesnt_serve_non_bedrock_projects()
    {
        $driver = new BedrockCrestDriver();

        $this->assertFalse($driver->serves($this->projectDir('public-with-index-non-laravel'), 'my-site', '/'));
    }

    public function test_it_gets_front_controller()
    {
        $driver = new BedrockCrestDriver();

        $projectPath = $this->projectDir('bedrock');
        $this->assertEquals($projectPath.'/web/index.php', $driver->frontControllerPath($projectPath, 'my-site', '/'));
    }
}
