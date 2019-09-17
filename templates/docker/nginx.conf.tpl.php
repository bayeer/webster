<?php
return $conf = <<<EOF
user  nginx;
worker_processes  4;
#daemon off;

error_log  /var/log/nginx/error.log warn;
pid        /var/run/nginx.pid;

events {
    worker_connections  1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;
    
    #access_log  /var/log/nginx/access.log;
    # Switch logging to console out to view via Docker
    access_log /dev/stdout;
    error_log /dev/stderr;
    
    sendfile        on;
    keepalive_timeout  65;
    
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-available/*.conf;
}

EOF;
