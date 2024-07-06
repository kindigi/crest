<?php

namespace Crest;

class DnsMasq
{
    public $dnsmasqMasterConfigFile = BREW_PREFIX.'/etc/dnsmasq.conf';

    public $dnsmasqSystemConfDir = BREW_PREFIX.'/etc/dnsmasq.d';

    public $resolverPath = '/etc/resolver';

    public function __construct(public Brew $brew, public CommandLine $cli, public Filesystem $files, public Configuration $configuration) {}

    /**
     * Install and configure DnsMasq.
     */
    public function install(string $tld = 'test'): void
    {
        $this->brew->ensureInstalled('dnsmasq');

        // For DnsMasq, we enable its feature of loading *.conf from /usr/local/etc/dnsmasq.d/
        // and then we put a crest config file in there to point to the user's home .config/crest/dnsmasq.d
        // This allows Crest to make changes to our own files without needing to modify the core dnsmasq configs
        $this->ensureUsingDnsmasqDForConfigs();

        $this->createDnsmasqTldConfigFile($tld);

        $this->createTldResolver($tld);

        $this->brew->restartService('dnsmasq');

        info('Crest is configured to serve for TLD [.'.$tld.']');
    }

    /**
     * Forcefully uninstall dnsmasq.
     */
    public function uninstall(): void
    {
        $this->brew->stopService('dnsmasq');
        $this->brew->uninstallFormula('dnsmasq');
        $this->cli->run('rm -rf '.BREW_PREFIX.'/etc/dnsmasq.d/dnsmasq-crest.conf');

        // As Laravel Herd uses the same DnsMasq resolver, we should only
        // delete it if Herd is not installed.
        if (! $this->files->exists('/Applications/Herd.app')) {
            $tld = $this->configuration->read()['tld'];
            $this->files->unlink($this->resolverPath.'/'.$tld);
        }
    }

    /**
     * Stop the dnsmasq service.
     */
    public function stop(): void
    {
        $this->brew->stopService(['dnsmasq']);
    }

    /**
     * Tell Homebrew to restart dnsmasq.
     */
    public function restart(): void
    {
        $this->brew->restartService('dnsmasq');
    }

    /**
     * Ensure the DnsMasq configuration primary config is set to read custom configs.
     */
    public function ensureUsingDnsmasqDForConfigs(): void
    {
        info('Updating Dnsmasq configuration...');

        // set primary config to look for configs in /usr/local/etc/dnsmasq.d/*.conf
        $contents = $this->files->get($this->dnsmasqMasterConfigFile);
        // ensure the line we need to use is present, and uncomment it if needed
        if (strpos($contents, 'conf-dir='.BREW_PREFIX.'/etc/dnsmasq.d/,*.conf') === false) {
            $contents .= PHP_EOL.'conf-dir='.BREW_PREFIX.'/etc/dnsmasq.d/,*.conf'.PHP_EOL;
        }
        $contents = str_replace('#conf-dir='.BREW_PREFIX.'/etc/dnsmasq.d/,*.conf', 'conf-dir='.BREW_PREFIX.'/etc/dnsmasq.d/,*.conf', $contents);

        // remove entries used by older Crest versions:
        $contents = preg_replace('/^conf-file.*crest.*$/m', '', $contents);

        // save the updated config file
        $this->files->put($this->dnsmasqMasterConfigFile, $contents);

        // remove old ~/.config/crest/dnsmasq.conf file because things are moved to the ~/.config/crest/dnsmasq.d/ folder now
        if (file_exists($file = dirname($this->dnsmasqUserConfigDir()).'/dnsmasq.conf')) {
            unlink($file);
        }

        // add a crest-specific config file to point to user's home directory crest config
        $contents = $this->files->getStub('etc-dnsmasq-crest.conf');
        $contents = str_replace('CREST_HOME_PATH', CREST_HOME_PATH, $contents);
        $this->files->ensureDirExists($this->dnsmasqSystemConfDir, user());
        $this->files->putAsUser($this->dnsmasqSystemConfDir.'/dnsmasq-crest.conf', $contents);

        $this->files->ensureDirExists(CREST_HOME_PATH.'/dnsmasq.d', user());
    }

    /**
     * Create the TLD-specific dnsmasq config file.
     */
    public function createDnsmasqTldConfigFile(string $tld): void
    {
        $tldConfigFile = $this->dnsmasqUserConfigDir().'tld-'.$tld.'.conf';
        $loopback = $this->configuration->read()['loopback'];

        $this->files->putAsUser($tldConfigFile, 'address=/.'.$tld.'/'.$loopback.PHP_EOL.'listen-address='.$loopback.PHP_EOL);
    }

    /**
     * Create the resolver file to point the configured TLD to configured loopback address.
     */
    public function createTldResolver(string $tld): void
    {
        $this->files->ensureDirExists($this->resolverPath);
        $loopback = $this->configuration->read()['loopback'];

        $this->files->put($this->resolverPath.'/'.$tld, 'nameserver '.$loopback.PHP_EOL);
    }

    /**
     * Update the TLD/domain resolved by DnsMasq.
     */
    public function updateTld(string $oldTld, string $newTld): void
    {
        $this->files->unlink($this->resolverPath.'/'.$oldTld);
        $this->files->unlink($this->dnsmasqUserConfigDir().'tld-'.$oldTld.'.conf');

        $this->install($newTld);
    }

    /**
     * Refresh the DnsMasq configuration.
     */
    public function refreshConfiguration(): void
    {
        $tld = $this->configuration->read()['tld'];

        $this->updateTld($tld, $tld);
    }

    /**
     * Get the custom configuration path.
     */
    public function dnsmasqUserConfigDir(): string
    {
        return $_SERVER['HOME'].'/.config/crest/dnsmasq.d/';
    }
}
