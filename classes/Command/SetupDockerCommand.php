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



class SetupDockerCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Try like this:
> ./webster setup-docker ~/Dev/php/hello-lara/ localhost:3001 --type=laravel"

You will need to enter 'sudo' password to write to /etc/hosts file. 

Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('setup-docker')
            ->setDescription('Sets up docker containers for the project')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('projectdir', InputArgument::REQUIRED, 'Project directory.'),
                    new InputArgument('proxyhost', InputArgument::REQUIRED, 'Proxied host name.'),
                    new InputOption('type', 'type', InputArgument::OPTIONAL, 'Project framework/cms type', 'simple'),
                    new InputOption('dbdir', 'dbdir', InputArgument::OPTIONAL, 'Directory path for db', ''),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir     = $input->getArgument('projectdir');
        $proxyHost      = $input->getArgument('proxyhost');
        $projectType    = $input->getOption('type');
        $dbDir          = $input->getOption('dbdir');


        $output->writeln([
            'Creating docker-compose containers',
            '---',
        ]);

        $docker = new DockerCompose($this, $input, $output, $projectDir);
        $docker->up($proxyHost, $dbDir, $projectType, $charset='utf8', $distro='yes');

        $output->writeln([
            '---',
            'Docker-compose containers successfully created.',
            ''
        ]);
    }
}