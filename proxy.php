<?php

# 1. if we visit this php file directly via http://grepmusic.com/path/to/proxy.php, then we stop proxy this time
if($_SERVER['SERVER_NAME'] !== 'g.grepmusic.com')
  die('Bad Request');

# 2. if the url is a google Jump Link, then we redirect it by ourselves to avoid visiting *ugly and slow* google's jump page
$uri = $_SERVER['REQUEST_URI'];
if(isset($_GET['url']) && substr($uri, 0, 5) === '/url?') {
// we use jump link to stop (non-opera) browser from sending HTTP Referer header to keep our website avaliable
header('Content-Type: text/html');
?>
<!doctype html>
<html>
<body>
<script type="text/javascript">
if( /Chrome|Safari/i.test(navigator.userAgent) ) {
  window.onload = function () { t.click(); }
} else {
  document.writeln(<?php echo json_encode('<meta http-equiv="refresh" content="0; URL=' . htmlentities($_GET["url"], ENT_QUOTES,'UTF-8') . '">'); ?>);
}
</script>
<a style="display: none;" id="t"  href="<?php echo htmlentities($_GET["url"], ENT_QUOTES,'UTF-8'); ?>" rel="noreferrer">click</a>
</body>
</html>
<?php
  exit;
  // reserve the original jump method
  // die('<meta rel="noreferrer" http-equiv="refresh" content="0; URL=' . htmlentities($_GET["url"], ENT_QUOTES,'UTF-8') . '">');
  // header('Location: ' . $_GET['url']); exit;
}

# 3. get request headers
$request_headers = array();
foreach ($_SERVER as $k => $v) {
  if( $k === 'HTTP_HOST' ) continue;
  if(substr($k, 0, 5) === 'HTTP_') {
    $k = str_replace('_', '-', substr($k, 5));
/*     if(strtolower($k) === 'user-agent') {
       if(stripos($v, ' msie ') !== false) {
         // $v = 'Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1)';
       }
       // $v = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36';
     } */
    $request_headers[] = $k . ': ' . $v;
  }
}

# var_dump($_SERVER);
# var_dump($request_headers);
# exit;

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
$content_type = false;
$content_encoding = '';
foreach (explode("\r\n", $header) as $h) {
  $_h = strtolower(substr($h, 0, 17));
  if($_h === 'content-encoding:') {
    $content_encoding = substr($h, 17);
  } else if(substr($_h, 0, 13) === 'content-type:') {
    $content_type = substr($h, 13);
  } else if(substr($_h, 0, 11) === 'set-cookie:') {
    $h = false;
    // $h = preg_replace('/(?:(?:www)??\.)?google\.com(?:\.\w+)??/i', 'g.grepmusic.com', $h, 1); // enable cookie
  }
  $h !== false && header($h, false);
}

if ($content_type && stripos($content_type, 'text/html') === false) {
  die($body);
}
// else if it is an html file, we add our js file

// you can use copy h.js code here, but it is better you store it into a js file so that browser can cache it
$js = '<script src="http://grepmusic.com/public/h.js" type="text/javascript"></script>';

if(stripos($content_encoding, 'gzip') === false)
  die($body . $js);

// gzip encoding
echo gzencode( gzdecode($body) . $js );
// echo preg_replace('/(<\/head[^>]*+>)/i', $js . '$1', $body, 1);
?>
