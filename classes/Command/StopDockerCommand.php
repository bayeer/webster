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



class StopDockerCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Try like this:
> ./webster stop-docker ~/Dev/php/hellolara/"

You will need to enter 'sudo' password to write to /etc/hosts file. 

Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('stop-docker')
            ->setDescription('Executes `docker-compose down` in project directory')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('projectdir' , InputArgument::REQUIRED, 'Project directory.'),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = $input->getArgument('projectdir');

        $output->writeln([
            'Halting docker containers...',
            '---',
        ]);

        $dir = expand_tilde($projectDir);
        if (! (file_exists($dir)) ) {
            die("Directory `{$projectDir}` not found." . PHP_EOL);
        }

        $docker = new DockerCompose($this, $input, $output, $projectDir);
        $docker->down();

        $output->writeln([
            '---',
            'Docker containers successfully stopped.',
            ''
        ]);
    }
}