<?php

namespace Crest;

class PhpMyAdmin
{

    private const CONFIG_PATH = '/opt/homebrew/etc/phpmyadmin.config.inc.php';
    private const PHPMYADMIN_PATH = '/opt/homebrew/share/phpmyadmin';

    public function __construct(
        public Brew          $brew,
        public Site          $site,
    ){}

    public function install(): void
    {
        $this->brew->ensureInstalled('phpmyadmin');

        $name = basename(self::PHPMYADMIN_PATH);

        $this->site->link(self::PHPMYADMIN_PATH, $name);

        $url = $this->site->domain($name);

        $this->site->secure($url, null, 368);

        $this->setConfig('blowfish_secret', $this->generateBlowFish());

        $this->brew->restartService($this->brew->nginxServiceName());
    }

    public function uninstall(): void
    {
        $this->brew->stopService('phpmyadmin');
        $this->brew->uninstallFormula('phpmyadmin');
    }

    private function setConfig($key, $value): void
    {
        $path = self::CONFIG_PATH;
        $config = file_get_contents($path);
        $config = preg_replace("/\\\$cfg\['$key'\] = '.*';/", "\$cfg['$key'] = '$value';", $config);
        file_put_contents($path, $config);
    }

    private function generateBlowFish(): string
    {
        $blowfish_secret = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        for ($i = 0; $i < 64; $i++) {
            $blowfish_secret .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $blowfish_secret;
    }
}
