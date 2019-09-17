<?php
/**
 * Expands `~/` in POSIX paths
 * @see https://compwright.com/2013-09-03/tilde-expansion-in-php/
 *
 * @param $path
 * @return mixed
 */
function expand_tilde($path)
{
    if (function_exists('posix_getuid') && strpos($path, '~') !== false) {
        $info = posix_getpwuid(posix_getuid());
        $path = str_replace('~', $info['dir'], $path);
    }

    return $path;
}


function domain_basename($domain)
{
    $extension = '.loc';
    $basename = substr($domain, 0, strrpos($domain, $extension));

    return $basename;
}


function extract_proxy_port($proxyHost)
{
    $parts = explode(':', $proxyHost);
    $port = $parts[1];
    return $port;
}

function is_port_available($ip, $port)
{
    $fp = @fsockopen($ip, $port);
    if (is_resource($fp)) {
        fclose($fp);
        return TRUE;
    }
    return FALSE;
}