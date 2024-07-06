<?php

namespace Crest;

use DomainException;
use Illuminate\Support\Collection;

class Nginx
{
    const NGINX_CONF = BREW_PREFIX.'/etc/nginx/nginx.conf';

    public function __construct(public Brew $brew, public CommandLine $cli, public Filesystem $files,
        public Configuration $configuration, public Site $site) {}

    /**
     * Install the configuration files for Nginx.
     */
    public function install(): void
    {
        if (! $this->brew->hasInstalledNginx()) {
            $this->brew->installOrFail('nginx', []);
        }

        $this->installConfiguration();
        $this->installServer();
        $this->installNginxDirectory();
    }

    /**
     * Install the Nginx configuration file.
     */
    public function installConfiguration(): void
    {
        info('Installing nginx configuration...');

        $contents = $this->files->getStub('nginx.conf');

        $this->files->putAsUser(
            static::NGINX_CONF,
            str_replace(['CREST_USER', 'CREST_HOME_PATH'], [user(), CREST_HOME_PATH], $contents)
        );
    }

    /**
     * Install the Crest Nginx server configuration file.
     */
    public function installServer(): void
    {
        $this->files->ensureDirExists(BREW_PREFIX.'/etc/nginx/crest');

        $this->files->putAsUser(
            BREW_PREFIX.'/etc/nginx/crest/crest.conf',
            str_replace(
                ['CREST_HOME_PATH', 'CREST_SERVER_PATH', 'CREST_STATIC_PREFIX'],
                [CREST_HOME_PATH, CREST_SERVER_PATH, CREST_STATIC_PREFIX],
                $this->site->replaceLoopback($this->files->getStub('crest.conf'))
            )
        );

        $this->files->putAsUser(
            BREW_PREFIX.'/etc/nginx/fastcgi_params',
            $this->files->getStub('fastcgi_params')
        );
    }

    /**
     * Install the Nginx configuration directory to the ~/.config/crest directory.
     *
     * This directory contains all site-specific Nginx servers.
     */
    public function installNginxDirectory(): void
    {
        info('Installing nginx directory...');

        if (! $this->files->isDir($nginxDirectory = CREST_HOME_PATH.'/Nginx')) {
            $this->files->mkdirAsUser($nginxDirectory);
        }

        $this->files->putAsUser($nginxDirectory.'/.keep', PHP_EOL);

        $this->rewriteSecureNginxFiles();
    }

    /**
     * Check nginx.conf for errors.
     */
    private function lint(): void
    {
        $this->cli->run(
            'sudo nginx -c '.static::NGINX_CONF.' -t',
            function ($exitCode, $outputMessage) {
                throw new DomainException("Nginx cannot start; please check your nginx.conf [$exitCode: $outputMessage].");
            }
        );
    }

    /**
     * Generate fresh Nginx servers for existing secure sites.
     */
    public function rewriteSecureNginxFiles(): void
    {
        $tld = $this->configuration->read()['tld'];
        $loopback = $this->configuration->read()['loopback'];

        if ($loopback !== CREST_LOOPBACK) {
            $this->site->aliasLoopback(CREST_LOOPBACK, $loopback);
        }

        $config = compact('tld', 'loopback');

        $this->site->resecureForNewConfiguration($config, $config);
    }

    /**
     * Restart the Nginx service.
     */
    public function restart(): void
    {
        $this->lint();

        $this->brew->restartService($this->brew->nginxServiceName());
    }

    /**
     * Stop the Nginx service.
     */
    public function stop(): void
    {
        $this->brew->stopService(['nginx']);
    }

    /**
     * Forcefully uninstall Nginx.
     */
    public function uninstall(): void
    {
        $this->brew->stopService(['nginx', 'nginx-full']);
        $this->brew->uninstallFormula('nginx nginx-full');
        $this->cli->quietly('rm -rf '.BREW_PREFIX.'/etc/nginx '.BREW_PREFIX.'/var/log/nginx');
    }

    /**
     * Return a list of all sites with explicit Nginx configurations.
     */
    public function configuredSites(): Collection
    {
        return collect($this->files->scandir(CREST_HOME_PATH.'/Nginx'))
            ->reject(function ($file) {
                return starts_with($file, '.');
            });
    }
}
