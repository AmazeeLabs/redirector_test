<?php
//error_reporting(0);

// DONT CACHE!
header("Expires: Sun, 07 Aug 1987 12:34:56 GMT");
header('Cache-Control: no-cache');
header('Pragma: no-cache');

// include the library
include 'dispatch.php';

// load the configuration
config('source', 'config/config.ini');
// define your routes

if (in_array($_SERVER['HTTP_HOST'], config('servicehost'))) {
    echo "Service is up - You are connecting with a Servicehost";
    die();
}

get('/index', function () {
    $http_host = strtolower($_SERVER['HTTP_HOST']);
    $domains = cache('domains', function() {
        _log(date(DATE_ISO8601).' - [INFO] - Reloading Data');
        $data = glob('redir/*');

        foreach ($data as $d) {
            // load redirections from filesystem
            $redirect[str_replace('redir/', '',$d)] = file_get_contents($d);
            // modify array key to match www. domains too
            $redirect[str_replace('redir/', 'www.',$d)] = file_get_contents($d);
        }

        return $redirect;
    },config('cache.ttl'));

    if(array_key_exists($http_host, $domains)) {
        _log(date(DATE_ISO8601).' - [SUCCESS] - REDIRECTED '.$http_host.' >> '.trim($domains[$http_host]));
        redirect($domains[$http_host]);
    }
    else
    {
        echo "No configuration found for this Domain! - " .$http_host ;
        _log(date(DATE_ISO8601).' - [ERROR] - Domain '.$http_host.' has no config');
    }

});
get('/clearcache', function(){
    cache_invalidate('domains');
    echo "Cache flushed";
    _log(date(DATE_ISO8601).' - [INFO] - Cache flushed');

});
// serve your site
dispatch();
?>