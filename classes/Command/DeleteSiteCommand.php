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



class DeleteSiteCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Creates site `domain` to `dir`.

Try like this:
> ./webster delete-site -f hellolara.loc --dir=~/Dev/php/hellolara/ --dbdir=~/DockerDatabases/mysql/hellolara/
or
> ./webster delete-site hellolara.loc --dir=~/Dev/php/hellolara/ --dbdir=~/DockerDatabases/mysql/hellolara/

You will need to enter 'sudo' password to write to `/etc/hosts` file.
 
---
Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('delete-site')
            ->setDescription('Sets up docker containers for the project')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('domain' , InputArgument::REQUIRED, 'Site domain name.'),
                    new InputOption('dir', 'd', InputArgument::OPTIONAL, 'Project directory.', ''),
                    new InputOption('force', 'f', InputArgument::OPTIONAL, 'Force to delete', null),
                    new InputOption('dbdir', 'db', InputArgument::OPTIONAL, 'Directory path for db', ''),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain         = $input->getArgument('domain');
        $projectDir     = $input->getOption('dir');
        $dbDir          = $input->getOption('dbdir');
        $force          = $input->getOption('force');



        $output->writeln([
            "Deleting site `{$domain}` in `{$projectDir}`...",
            '---',
        ]);

        $webster = new Webster();
        $webster->deleteSite($this, $input, $output, $domain, $projectDir, $dbDir, $force);

        $output->writeln(['',
            "* Restarting local nginx...",
        ] );

        Webster::restartLocalNginx();

        $output->writeln("* [OK]");

        $output->writeln([
            '---',
            'Site successfully deleted.',
            ''
        ]);
    }
}