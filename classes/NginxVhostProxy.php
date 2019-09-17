<?php
namespace Webster;



class NginxVhostProxy
{
    private $ngxConfDir;
    private $ngxAvailableDir;
    private $ngxEnabledDir;
    private $confTemplatePath;



    public function __construct($ngxConfDir         = '/etc/nginx/',
                                $ngxAvailableDir    = '/etc/nginx/sites-available/',
                                $ngxEnabledDir      = '/etc/nginx/sites-enabled/',
                                $confTemplatePath   = '')
    {
        $this->ngxConfDir       = $ngxConfDir;
        $this->ngxAvailableDir  = $ngxAvailableDir;
        $this->ngxEnabledDir    = $ngxEnabledDir;


        if (!$confTemplatePath) {
            $confTemplatePath = realpath(__DIR__ . '/../') . '/templates/nginx_rproxy_conf_tpl.php';
        }
        if (!file_exists($confTemplatePath)) {
            die("Could not find config file '{$confTemplatePath}'" . PHP_EOL);
        }
        $this->confTemplatePath = $confTemplatePath;
    }



    // PUBLIC METHODS

    public function add($domain, $proxyHost)
    {
        // reading conf template file to string
        $nginxConf = include($this->confTemplatePath);


        // writing proxy virtual host
        $this->saveNginxConfigFile($nginxConf, $domain);

        $this->enable($domain);
    }


    public function remove($domain)
    {
        // deleting site conf from /etc/nginx/sites-available/
        $this->disable($domain);

        $this->deleteNginxConfigFile($domain);
    }



    // PRIVATE METHODS


    protected function enable($domain)
    {
        // creating symbolic link to /etc/nginx/sites-enabled/@$domain

        if (FALSE === symlink($this->ngxAvailableDir.$domain, $this->ngxEnabledDir.$domain)) {
            die("Couldn't create symbolic link to '{$this->ngxEnabledDir}@{$domain}'" . PHP_EOL);
        }
    }


    protected function disable($domain)
    {
        // deleting site symlink from from /etc/nginx/sites-enabled/

        try {
            unlink($this->ngxEnabledDir.$domain);
        }
        catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }


    protected function saveNginxConfigFile($nginxConfig, $domain) {
        $filepath = $this->ngxAvailableDir . $domain;

        if (FALSE === file_put_contents($filepath, $nginxConfig)) {
            die("Couldn't create config file: '{$filepath}'" . PHP_EOL);
        }
        return true;
    }


    protected function deleteNginxConfigFile($domain)
    {
        try {
            unlink($this->ngxAvailableDir.$domain);
        }
        catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }
}


