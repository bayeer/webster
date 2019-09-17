<?php
return $conf = <<<EOF
server {
    listen 80;
    server_name {$domain};

    access_log off;
    error_log off;

    client_max_body_size 1024M;
    client_body_buffer_size 4M;

    location / {
        proxy_pass http://{$proxyHost};
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
        allow 127.0.0.1;
        deny all;
    }
}
EOF;
