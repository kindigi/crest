<?php

// Allow bypassing these checks if using Crest in a non-CLI app
if (php_sapi_name() !== 'cli') {
    return;
}

/**
 * Check the system's compatibility with Crest.
 */
$inTestingEnvironment = strpos($_SERVER['SCRIPT_NAME'], 'phpunit') !== false;

if (PHP_OS !== 'Darwin' && ! $inTestingEnvironment) {
    echo 'Crest only supports the Mac operating system.'.PHP_EOL;

    exit(1);
}

if (version_compare(PHP_VERSION, '8.0', '<')) {
    echo 'Crest requires PHP 8.0 or later.';

    exit(1);
}

if (exec('which brew') == '' && ! $inTestingEnvironment) {
    echo 'Crest requires Homebrew to be installed on your Mac.';

    exit(1);
}
