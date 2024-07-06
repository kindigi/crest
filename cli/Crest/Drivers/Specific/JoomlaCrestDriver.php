<?php

namespace Crest\Drivers\Specific;

use Crest\Drivers\BasicCrestDriver;

class JoomlaCrestDriver extends BasicCrestDriver
{
    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return is_dir($sitePath.'/libraries/joomla');
    }

    /**
     * Take any steps necessary before loading the front controller for this driver.
     */
    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        $_SERVER['PHP_SELF'] = $uri;
    }
}
