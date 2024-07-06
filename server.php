<?php

require_once './cli/includes/require-drivers.php';
require_once './cli/Crest/Server.php';

use Crest\Drivers\CrestDriver;
use Crest\Server;

/**
 * Define the user's "~/.config/crest" path.
 */
defined('CREST_HOME_PATH') or define('CREST_HOME_PATH', posix_getpwuid(fileowner(__FILE__))['dir'].'/.config/crest');
defined('CREST_STATIC_PREFIX') or define('CREST_STATIC_PREFIX', '41c270e4-5535-4daa-b23e-c269744c2f45');

/**
 * Load the Crest configuration.
 */
$crestConfig = json_decode(
    file_get_contents(CREST_HOME_PATH.'/config.json'), true
);

/**
 * If the HTTP_HOST is an IP address, check the start of the REQUEST_URI for a
 * valid hostname, extract and use it as the effective HTTP_HOST in place
 * of the IP. It enables the use of Crest in a local network.
 */
if (Server::hostIsIpAddress($_SERVER['HTTP_HOST'])) {
    $uriForIpAddressExtraction = ltrim($_SERVER['REQUEST_URI'], '/');

    if ($host = Server::crestSiteFromIpAddressUri($uriForIpAddressExtraction, $crestConfig['tld'])) {
        $_SERVER['HTTP_HOST'] = $host;
        $_SERVER['REQUEST_URI'] = str_replace($host, '', $uriForIpAddressExtraction);
    }
}

$server = new Server($crestConfig);

/**
 * Parse the URI and site / host for the incoming request.
 */
$uri = Server::uriFromRequestUri($_SERVER['REQUEST_URI']);
$siteName = $server->siteNameFromHttpHost($_SERVER['HTTP_HOST']);
$crestSitePath = $server->sitePath($siteName);

if (is_null($crestSitePath) && is_null($crestSitePath = $server->defaultSitePath())) {
    Server::show404();
}

$crestSitePath = realpath($crestSitePath);

/**
 * Find the appropriate Crest driver for the request.
 */
$crestDriver = CrestDriver::assign($crestSitePath, $siteName, $uri);

if (! $crestDriver) {
    Server::show404();
}

/**
 * ngrok uses the X-Original-Host to store the forwarded hostname.
 */
if (isset($_SERVER['HTTP_X_ORIGINAL_HOST']) && ! isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = $_SERVER['HTTP_X_ORIGINAL_HOST'];
}

/**
 * Attempt to load server environment variables.
 */
$crestDriver->loadServerEnvironmentVariables(
    $crestSitePath, $siteName
);

/**
 * Allow driver to mutate incoming URL.
 */
$uri = $crestDriver->mutateUri($uri);

/**
 * Determine if the incoming request is for a static file.
 */
$isPhpFile = pathinfo($uri, PATHINFO_EXTENSION) === 'php';

if ($uri !== '/' && ! $isPhpFile && $staticFilePath = $crestDriver->isStaticFile($crestSitePath, $siteName, $uri)) {
    return $crestDriver->serveStaticFile($staticFilePath, $crestSitePath, $siteName, $uri);
}

/**
 * Allow for drivers to take pre-loading actions (e.g. setting server variables).
 */
$crestDriver->beforeLoading($crestSitePath, $siteName, $uri);

/**
 * Attempt to dispatch to a front controller.
 */
$frontControllerPath = $crestDriver->frontControllerPath(
    $crestSitePath, $siteName, $uri
);

if (! $frontControllerPath) {
    if (isset($crestConfig['directory-listing']) && $crestConfig['directory-listing'] == 'on') {
        Server::showDirectoryListing($crestSitePath, $uri);
    }

    Server::show404();
}

chdir(dirname($frontControllerPath));

require $frontControllerPath;
