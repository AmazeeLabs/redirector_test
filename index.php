<?php
//error_reporting(0);
// DONT CACHE!
header("Expires: Sun, 07 Aug 1987 12:34:56 GMT");
header('Cache-Control: no-cache');
header('Pragma: no-cache');
// include the library
include 'vendor/autoload.php';
// load the configuration
config('source', 'config/config.ini');
// define your routes
if (in_array($_SERVER['HTTP_HOST'], config('servicehost'))) {
    echo "Service is up - You are connecting with a Servicehost";
    die();
}

// Handle Paths. If we encounter a path we dynamically use this as our router if not we use the /
if (isset($_SERVER['REDIRECT_URL']))
{
    $url = $_SERVER['REDIRECT_URL'];
}
else
{
    $url = '';

}

on('*', $url, function () {

    $http_host = strtolower($_SERVER['HTTP_HOST']);

    $domains = cache('domains', function(){
        send_remote_syslog('[INFO] - Reloading Data', '190');
        $data = glob('redir/*');

        foreach ($data as $d) {
            // load redirections from filesystem
            $redirect[strtolower(str_replace('redir/', '', $d)) ] = trim(file_get_contents($d));
            // modify array key to match www. domains too
            $redirect[strtolower(str_replace('redir/', 'www.', $d)) ] = trim(file_get_contents($d));
        }
        return $redirect;

    },config('cache.ttl'));

    if (array_key_exists($http_host, $domains)) {
        send_remote_syslog('[SUCCESS] - REDIRECTED '.$http_host.' >> '.trim($domains[$http_host]), '189');
        // Removing / since this would make the redirects sometimes unusable
        $request_uri = trim($_SERVER['REQUEST_URI'], '/');
        redirect($domains[$http_host] . $request_uri, 301);

    } else {
        echo "No configuration found for this Domain! - " . $http_host;
        send_remote_syslog('[ERROR] - Domain has no config: '.$http_host, '187');
    }
});

on('GET', '/clearcache', function(){
    cache_invalidate('domains');
    echo "Cache flushed";
    send_remote_syslog('[INFO] - Cache flushed', '190');

});
// Helper Function to send Syslog Events to our Graylog Server
function send_remote_syslog($message, $severity = '191', $component = "amazee_redirect_custom") {
  $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  foreach(explode("\n", $message) as $line) {
    $syslog_message = "<". $severity .">" . date('M d H:i:s ') . $_SERVER['SERVER_ADDR'] . ' ' . $component . ': ' . $line;
    socket_sendto($sock, $syslog_message, strlen($syslog_message), 0, 'amalabs3.nine.ch', 515);
  }
  socket_close($sock);
}

dispatch();
