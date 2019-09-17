<?php
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Webster\Command\SetupProxyVhostCommand;
use Webster\Command\DeleteProxyVhostCommand;
use Webster\Webster;



class SetupProxyVhostCommandTest extends TestCase
{
    public function testWeberAddProxy()
    {
        // parameters
        $conf           = Webster::getConf();
        $domain         = uniqid('site_') . '.loc';
        $proxyHost      = 'localhost:8901';


        // usage

        $app = new Application($conf['app_name'], $conf['app_version']);
        $app->add(new SetupProxyVhostCommand());
        $app->add(new DeleteProxyVhostCommand());


        // cmd: setup-proxy-vhost

        $command = $app->find('setup-proxy-vhost');
        $commandTester = new CommandTester($command);
        $commandTester->execute($args = [
                                            'command' => $command->getName(),
                                            'sitename' => $domain,
                                            'proxyhost' => $proxyHost,
                                        ]);

        $filepath = $conf['nginx_sites_enabled_dir'] . $domain;

        $this->assertTrue(file_exists($filepath), "Local nginx config file for `{$domain}` not found");


        // cmd: delete-proxy-vhost

        $command = $app->find('delete-proxy-vhost');
        $commandTester = new CommandTester($command);
        $commandTester->execute($args = [
                                            'command' => $command->getName(),
                                            'sitename' => $domain,
                                        ]);
        $this->assertFalse(file_exists($filepath), "Local nginx config file for `{$domain}` was not deleted");


        // restarting nginx

        echo PHP_EOL,
            "* Restarting local nginx...",
            PHP_EOL;
        Webster::restartLocalNginx();
    }
}