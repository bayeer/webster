<?php
namespace Webster\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Webster\Webster;



class SetupProxyVhostCommand extends Command
{
    protected function configure()
    {
        $conf = Webster::getConf();

        $help = <<<EOF
Try like this:
> ./webster setup-proxy-vhost site1.loc localhost:3001"

You will need to enter 'sudo' password to write to /etc/hosts file. 

Webster {$conf['app_version']} by Bayeer, {$conf['app_created_at']}

EOF;
        $this
            ->setName('setup-proxy-vhost')
            ->setDescription('Sets up virtual host proxying to localhost:{port}')
            ->setHelp($help)
            ->setDefinition(
                new InputDefinition(array(
                    new InputArgument('domain' , InputArgument::REQUIRED, 'Site domain name.'),
                    new InputArgument('proxyhost', InputArgument::REQUIRED, 'Proxied host name.'),
                ))
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain     = $input->getArgument('domain');
        $proxyHost  = $input->getArgument('proxyhost');


        $output->writeln([
            'Proxying virtual host',
            '---',
        ]);

        $webster = new Webster();
        $webster->setupProxyVhost($domain, $proxyHost);

        $output->writeln([
            $domain . "\t\t" . $proxyHost,
            '---',
            'Proxy virtual host successfully created.',
            ''
        ]);
    }
}