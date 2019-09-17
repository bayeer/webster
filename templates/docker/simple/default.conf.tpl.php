<?php
return $conf = <<<EOF
upstream php-upstream {
	server php-fpm:9000;
}

server {
    listen 80 default_server;
#    listen [::]:80 default_server ipv6only=on;

    server_name localhost;
    root /app;
    index index.php index.html index.htm;

    location ~ \.php\$ {
        try_files \$uri =404;
        fastcgi_pass php-upstream;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        #fixes timeouts
        fastcgi_read_timeout 600;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}

EOF;
