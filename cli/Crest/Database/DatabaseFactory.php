<?php

namespace Crest\Database;

use Crest\Brew;
use Crest\CommandLine;
use Crest\Configuration;
use Crest\Filesystem;

class DatabaseFactory
{
    public static function manager($manager = null): PostgreSql|MySql
    {
        $commandLine = new CommandLine();
        $filesystem = new Filesystem();
        $configuration = new Configuration($filesystem);
        $brew = new Brew($commandLine, $filesystem);

        if (!$manager){
            $config = $configuration->read();
            $manager = $config['database']['manager'];
        }

        return match ($manager) {
            'mysql' => new MySql(
                cli: $commandLine,
                files: $filesystem,
                config: $configuration,
                brew: $brew
            ),
            'postgresql' => new PostgreSql(),
            default => throw new \DomainException('Database manager not supported'),
        };
    }
}
