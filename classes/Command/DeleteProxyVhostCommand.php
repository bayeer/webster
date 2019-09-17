<?php
namespace Webster\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Webster\Webster;



class DeleteProxyVhostCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Try like this:
> ./webster delete-proxy-vhost site1.loc"

You will need to enter 'sudo' password to write to /etc/hosts file. 

Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('delete-proxy-vhost')
            ->setDescription('Deletes virtual host proxied to localhost:{port}')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('domain' , InputArgument::REQUIRED, 'Site domain name.'),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');

        $webster = new Webster();

        $output->writeln([
            'Deleting proxied virtual host',
            '==================',
        ]);

        $webster->deleteProxyVhost($domain);

        $output->writeln([
            $domain,
            '---',
            'Successfully deleted.',
            ''
        ]);
    }
}