<?php
// include the library
include 'dispatch.php';

// load the configuration
config('source', 'config/config.ini');

// define your routes
get('/index', function () {
    $domains = cache('domains', function() {
        _log(date(DATE_ISO8601).' - [INFO] - Reloading Data');
        $data = glob('redir/*');

        foreach ($data as $d) {
            $redirect[str_replace('redir/', '',$d)] = file_get_contents($d);
        }

        return $redirect;
    },config('cache.ttl'));

    if($domains[$_SERVER['HTTP_HOST']] == true) {
        _log(date(DATE_ISO8601).' - [SUCCESS] - REDIRECTED '.$_SERVER['HTTP_HOST'].' >> '.trim($domains[$_SERVER['HTTP_HOST']]));
        redirect(302, $domains[$_SERVER['HTTP_HOST']]);
        //header('X-Powered-by : AWESOME REDIRECTR!');
    }
    else
    {
        echo "No configuration found for this Domain!";
        _log(date(DATE_ISO8601).' - [ERROR] - Domain '.$_SERVER['HTTP_HOST'].' has no config');
    }

});
get('/clearcache', function(){
    cache_invalidate('domains');
    _log(date(DATE_ISO8601).' - [INFO] - Cache flushed');

});
// serve your site
dispatch();
?>