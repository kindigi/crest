<?php

namespace Crest;

use GuzzleHttp\Client;

class Crest
{
    public $crestBin = BREW_PREFIX.'/bin/crest';

    public function __construct(public CommandLine $cli, public Filesystem $files) {}

    /**
     * Symlink the Crest Bash script into the user's local bin.
     */
    public function symlinkToUsersBin(): void
    {
        $this->unlinkFromUsersBin();

        $this->cli->runAsUser('ln -s "'.realpath(__DIR__.'/../../crest').'" '.$this->crestBin);
    }

    /**
     * Remove the symlink from the user's local bin.
     */
    public function unlinkFromUsersBin(): void
    {
        $this->cli->quietlyAsUser('rm '.$this->crestBin);
    }

    /**
     * Determine if this is the latest version of Crest.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function onLatestVersion(string $currentVersion): bool
    {
        $url = 'https://api.github.com/repos/kindigi/crest/releases/latest';
        $response = json_decode((new Client())->get($url)->getBody());

        return version_compare($currentVersion, trim($response->tag_name, 'v'), '>=');
    }

    /**
     * Create the "sudoers.d" entry for running Crest.
     */
    public function createSudoersEntry(): void
    {
        $this->files->ensureDirExists('/etc/sudoers.d');

        $this->files->put('/etc/sudoers.d/crest', 'Cmnd_Alias CREST = '.BREW_PREFIX.'/bin/crest *
%admin ALL=(root) NOPASSWD:SETENV: CREST'.PHP_EOL);
    }

    /**
     * Remove the "sudoers.d" entry for running Crest.
     */
    public function removeSudoersEntry(): void
    {
        $this->cli->quietly('rm /etc/sudoers.d/crest');
    }

    /**
     * Run composer global diagnose.
     */
    public function composerGlobalDiagnose(): void
    {
        $this->cli->runAsUser('composer global diagnose');
    }

    /**
     * Run composer global update.
     */
    public function composerGlobalUpdate(): void
    {
        $this->cli->runAsUser('composer global update');
    }

    public function forceUninstallText(): string
    {
        return '<fg=red>NOTE:</>
<comment>Crest has attempted to uninstall itself, but there are some steps you need to do manually:</comment>

1. Run <info>php -v</info>, and also <info>which php</info>, to see what PHP version you are now really using.
2. Run <info>composer global update</info> to update your globally-installed Composer packages to work with your default PHP.
    NOTE: Composer may have other dependencies for other global apps you have installed, and those may not be compatible with your default PHP.
3. Finish removing any Composer fragments of Crest:
    Run <info>composer global remove kindigi/crest</info>
    and then <info>rm '.BREW_PREFIX.'/bin/crest</info> to remove the Crest bin link if it still exists.

Optional:
- <info>brew list --formula</info> will show any other Homebrew services installed, in case you want to make changes to those as well.
- <info>brew doctor</info> can indicate if there might be any broken things left behind.
- <info>brew cleanup</info> can purge old cached Homebrew downloads.

If you had customized your Mac DNS settings in System Preferences->Network, you will need to remove 127.0.0.1 from that list.

You may also want to open Keychain Access and search for <comment>crest</comment> to remove any leftover trust certificates.';
    }

    public function uninstallText(): string
    {
        return '
<info>You did not pass the <fg=red>--force</> parameter, so this will only return instructions on how to uninstall, not ACTUALLY uninstall anything.
A --force removal WILL delete your custom configuration information, so be sure to make backups first.</info>

IF YOU WANT TO UNINSTALL CREST MANUALLY, DO THE FOLLOWING...

<info>1. Crest Keychain Certificates</info>
Before removing Crest configuration files, we recommend that you run <comment>crest unsecure --all</comment> to clean up the certificates that Crest inserted into your Keychain.
Alternatively you can do a search for <comment>@kindigi.crest</comment> in Keychain Access and delete those certificates there manually.

<info>2. Crest Configuration Files</info>
You may remove your user-specific Crest config files by running:  <comment>rm -rf ~/.config/crest</comment>

<info>3. Remove Crest package</info>
You can run <comment>composer global remove kindigi/crest</comment> to uninstall the Crest package.

<info>4. Homebrew Services</info>
You may remove the core services (php, nginx, dnsmasq) by running: <comment>brew uninstall --force php nginx dnsmasq</comment>
You can then remove selected leftover configurations for these services manually in both <comment>'.BREW_PREFIX.'/etc/</comment> and <comment>'.BREW_PREFIX.'/logs/</comment>.
(If you have other PHP versions installed, run <info>brew list --formula | grep php</info> to see which versions you should also uninstall manually.)

<error>BEWARE:</error> Uninstalling PHP via Homebrew will leave your Mac with its original PHP version, which may not be compatible with other Composer dependencies you have installed. As a result, you may get unexpected errors.

If you have customized your Mac DNS settings in System Preferences->Network, you may need to add or remove 127.0.0.1 from the top of that list.

<info>5. GENERAL TROUBLESHOOTING</info>
If your reasons for considering an uninstall are more for troubleshooting purposes, consider running <comment>brew doctor</comment> and/or <comment>brew cleanup</comment> to see if any problems exist there.
Also consider running <comment>sudo nginx -t</comment> to test your nginx configs in case there are failures/errors there preventing nginx from running.
Most of the nginx configs used by Crest are in your <comment>~/.config/crest/Nginx</comment> directory.

You might also want to investigate your global Composer configs. Helpful commands include:
<comment>composer global update</comment> to apply updates to packages
<comment>composer global outdated</comment> to identify outdated packages
<comment>composer global diagnose</comment> to run diagnostics
            ';
    }
}
