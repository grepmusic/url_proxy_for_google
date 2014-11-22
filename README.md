url_proxy_for_google
====================

a simple url proxy for google in China using php and nginx

Requirements:

  server must not be in China, it is better with fast network speed between google.com server or China

  php and nginx must be installed

configuration with comment '# change it to your ...' is welcome to be changed to fit your taste

you may change two parameters: $domain (g.grepmusic.com) and $php_file_path (/path/to/proxy.php)

steps:

copy configuration in nginx.conf and append it to your nginx server configuration file 'http { }' block

copy proxy.php to your server, /path/to/proxy.php

make h.js as an url and replace the corresponding line in proxy.php (on line which contains http://grepmusic.com/public/h.js) with that url (http://grepmusic.com/public/h.js).

restart nginx and visit your domain to enjoy it!
