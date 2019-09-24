<?php
namespace Webster;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;



class DockerCompose
{
    protected $command;
    protected $input;
    protected $output;
    protected $projectDir;
    protected $oldCwd;
    protected $templatesDir;



    /**
     * DockerCompose constructor.
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $projectDir
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, $projectDir)
    {
        $this->command      = $command;
        $this->input        = $input;
        $this->output       = $output;
        $this->projectDir   = expand_tilde($projectDir);
        $this->templatesDir = Webster::getWebsterPath() . '/templates/docker/';
        $this->oldCwd       = getcwd();

        $this->createProjectDir();
        chdir($this->projectDir);
    }

    public function __destruct()
    {
        if (file_exists($this->oldCwd) && is_dir($this->oldCwd)) {
            chdir($this->oldCwd);
        }
    }

    
    
    // PUBLIC METHODS


    public function init($conf, $domain, $proxyHost, $dbDir='', $projectType='simple', $charset='utf8', $distro='yes')
    {
        $this->createDirs($dbDir);
        $this->writeProjectConfigs($conf, $domain, $proxyHost, $projectType, $dbDir, $charset);
        $this->writeNginxConfig();
        $this->writePhpDockerfile();
        $this->writePhpIni();

        if ($projectType == 'simple') {
            $this->writePhpHello();
        }
    }

    public function rm($dbDir='', $force=null)
    {
        if ($dbDir) {
            $dbDir = expand_tilde($dbDir);
        }
        else {
            $dbDir = "{$this->projectDir}db/mysql/";
        }

        $this->deleteDbDir($dbDir, $force);
        $this->deleteProjectDir($force);
    }


    public function up()
    {
        $this->executeDockerCompose($cmd = 'docker-compose up -d');
    }


    public function down()
    {
        $this->executeDockerCompose($cmd = 'docker-compose down');
    }


    public function restart()
    {
        $this->executeDockerCompose($cmd = 'docker-compose restart');
    }

    
    
    // PRIVATE METHODS

    private function createDirs($dbDir)
    {
        $dirs = [
            'confDir'       => $this->projectDir.'conf/',
            'srcDir'        => $this->projectDir.'src/',
            'phpDockerDir'  => $this->projectDir.'php/',
            'dbDir'         => $this->projectDir.'db/mysql/'
        ];
        if ($dbDir) {
            $dirs['dbDir'] = $dbDir;
        }

        $this->output->writeln([
            "* Creating project directories..."
        ]);
        if (FALSE === shell_exec("mkdir -p {$dirs['confDir']}")) {
            die("* Could not create dir: {$dirs['confDir']}" . PHP_EOL);
        }
        if (FALSE === shell_exec("mkdir -p {$dirs['srcDir']}")) {
            die("* Could not create dir: {$dirs['srcDir']}" . PHP_EOL);
        }
        if (FALSE === shell_exec("mkdir -p {$dirs['phpDockerDir']}")) {
            die("* Could not create dir: {$dirs['phpDockerDir']}" . PHP_EOL);
        }
        if (FALSE === shell_exec("mkdir -p {$dirs['dbDir']}")) {
            die("* Could not create dir: {$dirs['dbDir']}" . PHP_EOL);
        }
        $this->output->writeln([
            '* [OK]'
        ]);
    }


    private function writeProjectConfigs($conf, $domain, $proxyHost, $projectType='simple', $dbDir='', $charset='utf8')
    {
        $this->output->writeln([
            "* Creating `docker-compose.yml`...",
            "* Creating `conf/default.conf`...",
        ]);


        // * setting php flags in config
        $php_param_func_overload = "\tfastcgi_param PHP_ADMIN_VALUE \"mbstring.func_overload=2\";\n";
        if ($charset == 'cp1251') {
            $php_param_func_overload = "\tfastcgi_param PHP_ADMIN_VALUE \"mbstring.func_overload=0\";\n";
        }

        $proxyPort      = extract_proxy_port($proxyHost);
        $dbName         = domain_basename($domain);
        $dbRootPassw    = $conf['mysql']['root_password'];
        $dbPassw        = $conf['mysql']['default_password'];

        if ($dbDir) {
            $dbDir = expand_tilde($dbDir);
        }
        else {
            $dbDir = "{$this->projectDir}db/mysql/";
        }

        $dockerConf = include("{$this->templatesDir}{$projectType}/docker-compose.yml.tpl.php");
        $vhostConf  = include("{$this->templatesDir}{$projectType}/default.conf.tpl.php");

        if (FALSE === file_put_contents($this->projectDir . '/docker-compose.yml', $dockerConf)) {
            die("* Could not write docker-compose.yml in {$this->projectDir}");
        }
        if (FALSE === file_put_contents($this->projectDir . 'conf/default.conf', $vhostConf)) {
            die("* Could not write `{$this->projectDir}conf/default.conf`." . PHP_EOL);
        }
        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function writeNginxConfig()
    {
        $this->output->writeln([
            "* Creating `nginx.conf`..."
        ]);

        $conf = include("{$this->templatesDir}nginx.conf.tpl.php");

        if (FALSE === file_put_contents("{$this->projectDir}conf/nginx.conf", $conf)) {
            die("* Could not write `{$this->projectDir}conf/nginx.conf`." . PHP_EOL);
        }
        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function writePhpIni()
    {
        $this->output->writeln([
            "* Creating `conf/php.ini`..."
        ]);

        $ini = include("{$this->templatesDir}php.ini.tpl.php");

        if (FALSE === file_put_contents("{$this->projectDir}conf/php.ini", $ini)) {
            die("* Could not write `{$this->projectDir}conf/php.ini`." . PHP_EOL);
        }
        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function writePhpHello()
    {
        $this->output->writeln([
            "* Creating `src/index.php`..."
        ]);

        $content = <<<'EOF'
<?php
echo 'Test', PHP_EOL;
phpinfo();

EOF;

        if (FALSE === file_put_contents("{$this->projectDir}src/index.php", $content)) {
            die("* Could not write `{$this->projectDir}src/index.php`." . PHP_EOL);
        }
        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function writePhpDockerfile()
    {
        $this->output->writeln([
            "* Creating `php/Dockerfile`..."
        ]);

        $conf = include("{$this->templatesDir}Dockerfile.tpl.php");

        if (FALSE === file_put_contents("{$this->projectDir}php/Dockerfile", $conf)) {
            die("* Could not write `{$this->projectDir}php/Dockerfile`." . PHP_EOL);
        }
        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function executeDockerCompose($cmd = 'docker-compose up -d')
    {
        $this->output->writeln([
            "* Executing `{$cmd}`..."
        ]);

        // starting docker-compose via shell command
        if (FALSE === shell_exec($cmd)) {
            die("* Could not perform `{$cmd}`" . PHP_EOL);
        }

        $this->output->writeln([
            "* [OK]"
        ]);
    }


    private function createProjectDir()
    {
        if (!( file_exists($this->projectDir) && is_dir($this->projectDir) )) { // if projectDir doesn't exist
            $helper = $this->command->getHelper('question');
            $question = new ConfirmationQuestion("Directory `{$this->projectDir}` not found. Create? (y/N):", false);

            if (!$helper->ask($this->input, $this->output, $question)) {
                exit;
            }
            if (FALSE === shell_exec("mkdir -p {$this->projectDir}")) {
                die("* Could not create directory: {$this->projectDir}\nTry to change chmod or run script under sudo." . PHP_EOL);
            }
        }
    }


    private function deleteProjectDir($force=null)
    {
        if (( file_exists($this->projectDir) && is_dir($this->projectDir) )) { // if projectDir exist
            if (NULL === $force) {
                $helper = $this->command->getHelper('question');
                $question = new ConfirmationQuestion("Directory `{$this->projectDir}` will be deleted. Proceed? (y/N):", false);

                if (!$helper->ask($this->input, $this->output, $question)) {
                    exit;
                }
            }
            if (FALSE === shell_exec("rm -rf {$this->projectDir}")) {
                die("* Could not delete directory: {$this->projectDir}\nTry to change chmod or run script under sudo." . PHP_EOL);
            }
        }
    }


    private function deleteDbDir($dbDir, $force=null)
    {
        if (NULL === $force) {
            $helper = $this->command->getHelper('question');
            $question = new ConfirmationQuestion("Directory `{$dbDir}` will be deleted. Proceed? (y/N):", false);

            if (!$helper->ask($this->input, $this->output, $question)) {
                exit;
            }
        }
        if (FALSE === shell_exec("sudo rm -rf {$dbDir}")) {
            die("* Could not delete directory: {$dbDir}\nTry to change chmod or run script under sudo." . PHP_EOL);
        }
    }
}
