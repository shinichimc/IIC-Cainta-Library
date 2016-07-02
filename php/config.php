<?php

define('DSN','mysql:host=localhost; dbname=orbs');
define('DB_USER','root');
define('DB_PASSWORD','root');
define('EXPIRE_DURATION', 3);

define('SITE_URL','test.local/test/');
define('SITE_URL_ADMIN','');
define('PASSWORD_KEY','sophiechan');
define('ADMIN_USERNAME','0123456789');


error_reporting(E_ALL & ~E_NOTICE);

session_set_cookie_params(0,'/test/');

?>
