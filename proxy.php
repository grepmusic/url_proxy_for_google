<?php

# /path/to/proxy.php file

# 1. if we visit this php file directly via http://grepmusic.com/path/to/proxy.php, then we stop proxy this time, otherwise it will cause a path problem
if($_SERVER['SERVER_NAME'] !== 'g.grepmusic.com') # change it to your custom domain
  die('Bad Request');

# 2. if the url is a google Jump Link, then we redirect it by ourselves to avoid visiting *ugly and slow* google's jump page and improve speed
$uri = $_SERVER['REQUEST_URI'];
if(isset($_GET['url']) && substr($uri, 0, 5) === '/url?') {
  header('Location: ' . $_GET['url']); exit;
}

# 3. get request headers
$request_headers = array();
foreach ($_SERVER as $k => $v) {
  if( $k === 'HTTP_HOST' ) continue;
  if(substr($k, 0, 5) === 'HTTP_') {
    $request_headers[] = str_replace('_', '-', substr($k, 5)) . ': ' . $v;
  }
}

# var_dump($_SERVER); var_dump($request_headers); exit;

# 4. construct request packet
$options = array(
  CURLOPT_HTTPHEADER => $request_headers,
  CURLOPT_CUSTOMREQUEST => $_SERVER['REQUEST_METHOD'],
  CURLOPT_RETURNTRANSFER => 1,
#  CURLOPT_VERBOSE => 1,
  CURLOPT_HEADER => 1
);

# 5. send request to google and extract response headers and body
$url = 'https://www.google.com' . $uri;
$ch = curl_init($url);
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
curl_close($ch);

list($header, $body) = explode("\r\n\r\n", $response, 2);

# 6. send response(from google) to client
foreach (explode("\r\n", $header) as $h) { header($h, true); }
echo $body;
