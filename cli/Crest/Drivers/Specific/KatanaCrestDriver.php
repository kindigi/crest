<?php

namespace Crest\Drivers\Specific;

use Crest\Drivers\BasicCrestDriver;

class KatanaCrestDriver extends BasicCrestDriver
{
    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return file_exists($sitePath.'/katana');
    }

    /**
     * Mutate the incoming URI.
     */
    public function mutateUri(string $uri): string
    {
        return rtrim('/public'.$uri, '/');
    }
}
