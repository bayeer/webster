<?php
namespace Webster;



use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Webster
{
    protected $command;
    protected $conf; // config array
    protected $projectTypes = [
        'simple', 'laravel', 'yii1', 'yii2', 'yii2adv', 'bitrix', 'modx'
    ];



    public function __construct()
    {
        $this->conf = $conf = self::getConf();
        $this->resolveTildaDirs();
    }



    // PUBLIC METHODS

    public function setupSite(Command $command, InputInterface $input, OutputInterface $output,
                              $domain, $proxyHost, $projectDir='', $dbDir='', $projectType='simple', $charset='utf8', $distro='yes')
    {
        $projectDir = $this->resolveProjectDir($domain, $projectDir);

        if (!in_array($projectType, $this->projectTypes)) {
            die("Unknown project type {$projectType}" . PHP_EOL);
        }

        $this->setupProxyVhost($domain, $proxyHost);

        if (is_port_available('127.0.0.1', '3306')) { // if local MySQL is running
            $output->writeln('* Local MySQL is running! Stopping...' . PHP_EOL);
            $this->stopLocalMySQL();
            $output->writeln('* [OK]' . PHP_EOL);
        }

        $docker = new DockerCompose($command, $input, $output, $projectDir);
        $docker->init($this->conf, $domain, $proxyHost, $dbDir, $projectType, $charset, $distro);
        $docker->up();


        if ($distro == 'yes') {
            switch ($projectType) {
                case 'laravel':
                    $output->writeln("* Creating Laravel project via composer in `{$projectDir}`..." . PHP_EOL);
                    $this->execLaraComposer($projectDir);
                    $output->writeln("* [OK]" . PHP_EOL);

                    break;
                case 'yii2':

                    $output->writeln("* Creating Yii2 basic project via composer in `{$projectDir}`" . PHP_EOL);
                    $this->execYii2BasicComposer($projectDir);
                    $output->writeln("* [OK]" . PHP_EOL);

                    break;
                case 'yii2adv':

                    $output->writeln("* Creating Yii2 advanced project via composer in `{$projectDir}`" . PHP_EOL);
                    $this->execYii2AdvancedComposer($projectDir);
                    $output->writeln("* [OK]" . PHP_EOL);

                    break;
                case 'yii1':

                    $output->writeln("* Creating Yii1 project via composer in `{$projectDir}`" . PHP_EOL);
                    $this->execYii1Composer($projectDir);
                    $output->writeln("* [OK]" . PHP_EOL);

                    break;
                case 'bitrix':

                    $output->writeln("* Creating bitrix project in `{$projectDir}`" . PHP_EOL);
                    $this->processBitrix($command, $input, $output, $projectDir, $charset, $distro);
                    $output->writeln("* [OK]" . PHP_EOL);

                    break;
                case 'simple':
                default:
                    break;
            }
        }

    }


    public function deleteSite(Command $command, InputInterface $input, OutputInterface $output,
                               $domain, $projectDir, $dbDir='', $force=null)
    {
        $projectDir = $this->resolveProjectDir($domain, $projectDir);

        $docker = new DockerCompose($command, $input, $output, $projectDir);
        $docker->down();
        $docker->rm($dbDir, $force);

        $this->deleteProxyVhost($domain);
    }


    public function setupProxyVhost($domain, $proxyHost)
    {
        $conf = $this->conf;

        // setup vhosts
        $vhp = new NginxVhostProxy($conf['nginx_conf_dir'], $conf['nginx_sites_available_dir'], $conf['nginx_sites_enabled_dir']);
        $vhp->add($domain, $proxyHost);

        // write to /etc/hosts
        $ehm = new EtcHostsManager(Webster::getWebsterPath() . '/hosts');
        $ehm->add($domain);
        $ehm->save();
    }


    public function deleteProxyVhost($domain)
    {
        $conf = $this->conf;

        // setup vhosts
        $vhp = new NginxVhostProxy($conf['nginx_conf_dir'], $conf['nginx_sites_available_dir'], $conf['nginx_sites_enabled_dir']);
        $vhp->remove($domain);

        // write to /etc/hosts
        $ehm = new EtcHostsManager(Webster::getWebsterPath() . '/hosts');
        $ehm->remove($domain);
        $ehm->save();
    }


    public function execLaraComposer($projectDir)
    {
        // creating laravel project via composer
        $cmd = "docker run --rm --interactive --volume `pwd`/src:/app composer create-project laravel/laravel --prefer-dist /app && sudo chown -R \$USER:\$USER ./src && sudo find ./src/storage/ -type d -exec chmod 777 {} +";
        if (FALSE === shell_exec($cmd)) {
            die("Creating laravel project in `{$projectDir}` failed." . PHP_EOL);
        }
    }


    public function execYii2BasicComposer($projectDir)
    {
        // creating laravel project via composer
        $cmd = "docker run --rm --interactive --volume `pwd`/src:/app composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic /app && sudo chown -R \$USER:\$USER ./src && sudo find ./src/runtime/ -type d -exec chmod 777 {} +";
        if (FALSE === shell_exec($cmd)) {
            die("Creating yii2-basic project in `{$projectDir}` failed." . PHP_EOL);
        }
    }


    public function execYii2AdvancedComposer($projectDir)
    {
        // creating laravel project via composer
        $cmd = "docker run --rm --interactive --volume `pwd`/src:/app composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-advanced /app && sudo chown -R \$USER:\$USER ./src && sudo find ./src/frontend/runtime/ -type d -exec chmod 777 {} +";
        if (FALSE === shell_exec($cmd)) {
            die("Creating yii2-advanced project in `{$projectDir}` failed." . PHP_EOL);
        }
    }


    public function execYii1Composer($projectDir)
    {
        // creating laravel project via composer
        $cmd = "docker run --rm --interactive --volume `pwd`/src:/app composer create-project --prefer-dist --stability=dev yiisoft/yii /app && sudo chown -R \$USER:\$USER ./src && sudo find ./src/runtime/ -type d -exec chmod 777 {} +";
        if (FALSE === shell_exec($cmd)) {
            die("Creating yii1 project in `{$projectDir}` failed." . PHP_EOL);
        }
    }


    public function processBitrix(Command $command, InputInterface $input, OutputInterface $output, $projectDir,
                                  $charset, $distro)
    {
        $srcDir = $projectDir . '/src/';

        if ($distro == 'yes') {
            // 1 downloading bitrixsetup.php script
            $output->writeln("* Downloading `bitrixsetup.php` script to `{$srcDir}`..." . PHP_EOL);
            if (FALSE === shell_exec("wget -P {$srcDir} http://1c-bitrix.ru/download/scripts/bitrixsetup.php")) {
                die('Downloading http://1c-bitrix.ru/download/scripts/bitrixsetup.php failed.' . PHP_EOL);
            }

            // 2 create /local/ directories
            mkdir("{$srcDir}local/");
            mkdir("{$srcDir}local/components/");
            mkdir("{$srcDir}local/php_interface/");
            mkdir("{$srcDir}local/templates/");
            mkdir("{$srcDir}local/modules/");

            // 3 create /local/php_interface/init.php
            $init_file = <<<'EOF'
<?
// include iblock module
CModule::IncludeModule("iblock");

// include gpfunctions
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/gpfunctions.php");

EOF;
            file_put_contents("{$srcDir}local/php_interface/init.php", $init_file);

            // 5 create /local/php_interface/gpfunctions.php
            $gpfunctions = <<<'EOF'
<?php
// IBLOCKS
define('PHOTOGALLERY_IBLOCK_ID', 1);

// custom functions

function getIblockProperty($iblock_id, $elem_id, $prop_name) {
    $props = CIBlockElement::GetProperty($iblock_id, $elem_id, array("sort" => "asc"), Array("CODE"=>$prop_name));
    if ($ar_props = $props->Fetch()) {
        return $ar_props["VALUE"];
    }
    return NULL;
}

function getSections($iblock_id) {
    // собираем все разделы из информационного блока $ID
    $items = GetIBlockSectionList($iblock_id, null, Array("sort"=>"asc"), null);
    return $items;
}
EOF;
            file_put_contents("{$srcDir}local/php_interface/gpfunctions.php", $gpfunctions);
        }


        // 6. setting up chmod
        //chmod($srcDir.'/', 0777);
        shell_exec("find {$srcDir} -type d -exec chmod 777 {} +");
        shell_exec("find {$srcDir} -type f -exec chmod 666 {} +");
    }


    public function stopLocalMySQL()
    {
        // creating laravel project via composer
        if (FALSE === shell_exec("sudo systemctl stop mysql")) {
            die("Failed to stop Local MySQL service." . PHP_EOL);
        }
    }


    public static function restartLocalNginx()
    {
        // restarting nginx via shell command
        $conf = include(self::getWebsterPath() . '/includes/conf.php');
        if (FALSE === shell_exec($conf['nginx_restart_cmd'])) {
            die('Could not restart nginx' . PHP_EOL);
        }
    }


    public static function getWebsterPath() {
        return realpath(__DIR__ . '/../');
    }


    public static function getConf()
    {
        return include(self::getWebsterPath() . '/includes/conf.php');
    }


    private function resolveTildaDirs()
    {
        $this->conf['projects_dir']                 = expand_tilde($this->conf['projects_dir']);
        $this->conf['databases_dir']                = expand_tilde($this->conf['databases_dir']);
        $this->conf['nginx_sites_available_dir']    = expand_tilde($this->conf['nginx_sites_available_dir']);
        $this->conf['nginx_sites_enabled_dir']      = expand_tilde($this->conf['nginx_sites_enabled_dir']);
        $this->conf['nginx_conf_dir']               = expand_tilde($this->conf['nginx_conf_dir']);
        $this->conf['nginx_log_dir']                = expand_tilde($this->conf['nginx_log_dir']);
    }


    private function resolveProjectDir($domain, $projectDir)
    {
        if ( !(file_exists($this->conf['projects_dir']) && is_dir($this->conf['projects_dir'])) ) {
            die("Projects directory `{$projectDir}` doesn't exist.");
        }
        if (!$projectDir) {
            $projectDir = $this->conf['projects_dir'] . domain_basename($domain) . '/';
        }
        return $projectDir;
    }
}
