<?php
use PHPUnit\Framework\TestCase;
use Webster\NginxVhostProxy;
use Webster\Webster;



class NginxVhostProxyTest extends TestCase
{
    public function testRawAddProxy()
    {
        // parameters
        $conf            = Webster::getConf();
        $domain          = uniqid('site_') . '.loc';
        $proxyHost       = 'localhost:8901';

        $filename        = $conf['nginx_sites_available_dir'] . $domain;
        $filename2       = $conf['nginx_sites_enabled_dir'] . $domain;


        // usage
        $vhp = new NginxVhostProxy($conf['nginx_conf_dir'], $conf['nginx_sites_available_dir'], $conf['nginx_sites_enabled_dir']);

        echo PHP_EOL,
            "* Setting up reverse proxy config for domain '{$domain}' to '{$proxyHost}'...",
            PHP_EOL;
        echo '+ ', $filename , PHP_EOL;
        echo '+ ', $filename2, PHP_EOL;

        $vhp->add($domain, $proxyHost);

        $this->assertTrue(file_exists($filename));
        $this->assertTrue(file_exists($filename2));


        echo PHP_EOL,
            "* Deleting reverse proxy config for domain '{$domain}'...",
            PHP_EOL;
        echo '- ', $filename , PHP_EOL;
        echo '- ', $filename2, PHP_EOL;

        $vhp->remove($domain);

        $this->assertFalse(file_exists($filename));
        $this->assertFalse(file_exists($filename2));
    }
}