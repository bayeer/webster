<?php
namespace Webster\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Webster\DockerCompose;
use Webster\Webster;



class SetupSiteCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Creates site `domain` to `projectdir`.

Try like this:
> ./webster setup-site hellolara.loc
or
> ./webster setup-site hellolara.loc --dir=/Dev/php/hello-lara/ --proxyhost=localhost:3001 --type=laravel --dbdir=~/Dev/DockerDatabases/mysql/ --charset=utf8 --distro=yes
or
> ./webster setup-site hellolara.loc -d ~/Dev/php/hello-lara/ -px localhost:3001 -t laravel -db ~/Dev/php/hellolara/db/mysql/ -cs utf8 -ds yes


You will need to enter 'sudo' password to write to `/etc/hosts` file.
 
---
Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('setup-site')
            ->setDescription('Sets up docker containers for the project')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('domain' , InputArgument::REQUIRED, 'Site domain name.'),
                    new InputOption('dir', 'd', InputArgument::OPTIONAL, 'Project directory.', ''),
                    new InputOption('proxyhost', 'px', InputArgument::OPTIONAL, 'Proxied host name.', 'localhost:7000'),
                    new InputOption('type', 't', InputArgument::OPTIONAL, 'Project type (simple|laravel|yii2|bitrix|modx)', 'simple'),
                    new InputOption('dbdir', 'db', InputArgument::OPTIONAL, 'Directory path for db', ''),
                    new InputOption('charset', 'cs', InputArgument::OPTIONAL, 'The site character set', 'utf8'),
                    new InputOption('distro', 'ds', InputArgument::OPTIONAL, 'Download installation files', 'yes'),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain         = $input->getArgument('domain');
        $projectDir     = $input->getOption('dir');
        $proxyHost      = $input->getOption('proxyhost');
        $projectType    = $input->getOption('type');
        $dbDir          = $input->getOption('dbdir');
        $charset        = $input->getOption('charset');
        $distro         = $input->getOption('distro');


        $output->writeln([
            "Setting up site `{$domain}`...",
            '---',
        ]);

        // creates proxied virtual host: `domain.loc` -> `localhost:7000`
        $webster = new Webster();
        $webster->setupSite($this, $input, $output, $domain, $proxyHost, $projectDir, $dbDir, $projectType, $charset, $distro);


        $output->writeln(['',
            "* Restarting local nginx...",
        ] );

        Webster::restartLocalNginx();

        $output->writeln("* [OK]");


        $output->writeln([
            '---',
            'Site successfully created.',
            ''
        ]);
    }
}