<?php

define('DSN','mysql:host=localhost; dbname=events_tracking_system');
define('DB_USER','root');
define('DB_PASSWORD','');

// define('SITE_URL','localhost/test/');
define('PASSWORD_KEY','sophiechan');

error_reporting(E_ALL & ~E_NOTICE);

session_set_cookie_params(0,'/test/');

?>