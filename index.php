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
if (isset($_SERVER['PATH_INFO']))
{
    $url = $_SERVER['PATH_INFO'];
}
else
{
    $url = '/';

}

on('*', $url, function () {
    $http_host = strtolower($_SERVER['HTTP_HOST']);
    $data = glob('redir/*');

    foreach ($data as $d) {
        // load redirections from filesystem
        $redirect[strtolower(str_replace('redir/', '', $d)) ] = trim(file_get_contents($d));
        // modify array key to match www. domains too
        $redirect[strtolower(str_replace('redir/', 'www.', $d)) ] = trim(file_get_contents($d));
    }
    $domains = $redirect;
    if (array_key_exists($http_host, $domains)) {
        send_remote_syslog('[SUCCESS] - REDIRECTED '.$http_host.' >> '.trim($domains[$http_host]));
        redirect($domains[$http_host] . $_SERVER['REQUEST_URI'], 301);

    } else {
        echo "No configuration found for this Domain! - " . $http_host;
        send_remote_syslog('[ERROR] - Domain has no config: '.$http_host);
    }
});

// Helper Function to send Syslog Events to our Graylog Server
function send_remote_syslog($message, $component = "web", $program = "amazee-redirect-custom") {
  $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  foreach(explode("\n", $message) as $line) {
    $syslog_message = "<22>" . date('M d H:i:s ') . $program . ' ' . $component . ': ' . $line;
    socket_sendto($sock, $syslog_message, strlen($syslog_message), 0, 'amalabs2.nine.ch', 515);
  }
  socket_close($sock);
}

dispatch();
