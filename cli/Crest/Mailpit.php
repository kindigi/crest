<?php

namespace Crest;

class Mailpit
{
    public function __construct(
        public Brew $brew,
        public Site $site,
        public Configuration $config,
        public Filesystem $files
    ){}

    public function install(): void
    {
        $this->brew->ensureInstalled('mailpit');

        $this->restart();

        $this->site->proxyCreate(
            url: "mails",
            host: 'http://localhost:8025',
            secure: true
        );
    }

    public function restart(): void
    {
        $this->brew->restartService('mailpit');
    }

    public function stop(): void
    {
        $this->brew->stopService('mailpit');
    }

    public function uninstall(): void
    {
        $this->brew->uninstallFormula('mailpit');
        $this->files->unlink(BREW_PREFIX.'/log/mailpit.log');
    }
}
