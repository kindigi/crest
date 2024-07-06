<?php

namespace Crest;

use Configuration;
use Site;

class Upgrader
{
    public function __construct(public Filesystem $files) {}

    /**
     * Run all the upgrades that should be run every time Crest commands are run.
     */
    public function onEveryRun(): void
    {
        $this->pruneMissingDirectories();
        $this->pruneSymbolicLinks();
        $this->fixOldSampleCrestDriver();
        $this->errorIfOldCustomDrivers();
    }

    /**
     * Prune all non-existent paths from the configuration.
     */
    public function pruneMissingDirectories(): void
    {
        try {
            Configuration::prune();
        } catch (\JsonException $e) {
            warning('Invalid configuration file at '.Configuration::path().'.');
            exit;
        }
    }

    /**
     * Remove all broken symbolic links in the Crest config Sites diretory.
     */
    public function pruneSymbolicLinks(): void
    {
        Site::pruneLinks();
    }

    /**
     * If the user has the old `SampleCrestDriver` without the Crest namespace,
     * replace it with the new `SampleCrestDriver` that uses the namespace.
     */
    public function fixOldSampleCrestDriver(): void
    {
        $samplePath = CREST_HOME_PATH.'/Drivers/SampleCrestDriver.php';

        if ($this->files->exists($samplePath)) {
            $contents = $this->files->get($samplePath);

            if (! str_contains($contents, 'namespace')) {
                if ($contents !== $this->files->get(__DIR__.'/../stubs/Crest3SampleCrestDriver.php')) {
                    warning('Existing SampleCrestDriver.php has been customized.');
                    warning('Backing up at '.$samplePath.'.bak');

                    $this->files->putAsUser(
                        CREST_HOME_PATH.'/Drivers/SampleCrestDriver.php.bak',
                        $contents
                    );
                }

                $this->files->putAsUser(
                    CREST_HOME_PATH.'/Drivers/SampleCrestDriver.php',
                    $this->files->getStub('SampleCrestDriver.php')
                );
            }
        }
    }

    /**
     * Throw an exception if the user has old (non-namespaced) custom drivers.
     */
    public function errorIfOldCustomDrivers(): void
    {
        $driversPath = CREST_HOME_PATH.'/Drivers';

        if (! $this->files->isDir($driversPath)) {
            return;
        }

        foreach ($this->files->scanDir($driversPath) as $driver) {
            if (! ends_with($driver, 'CrestDriver.php')) {
                continue;
            }

            if (! str_contains($this->files->get($driversPath.'/'.$driver), 'namespace')) {
                warning('Please make sure all custom drivers have been upgraded for Crest 4.');
                warning('See the upgrade guide for more info:');
                warning('https://github.com/laravel/crest/blob/master/UPGRADE.md');
                exit;
            }
        }
    }
}
