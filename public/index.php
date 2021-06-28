<?php
require '../cgi-bin/vendor/autoload.php';

define('ENVIRONMENT', file_exists(__DIR__.'/../cgi-bin/src/development') ? 'dev' : 'prod');
define("ROOT_DIR", dirname(__DIR__));

session_start();
$config = array();

if (ENVIRONMENT == "prod")
{
    ini_set('display_errors', 0);
    if (version_compare(PHP_VERSION, '5.3', '>='))
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    }
    else
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
    }

    require '../cgi-bin/src/config.php';
}
else
{
    error_reporting(-1);
    ini_set('display_errors', 1);

    require '../cgi-bin/src/development/config.php';
}

$slim = new \Slim\App(['settings' => $config]);

require '../cgi-bin/src/init.php';
if (ENVIRONMENT == "dev") require '../cgi-bin/src/development/init.php';

require '../cgi-bin/src/routes.php';
if (ENVIRONMENT == "dev") require '../cgi-bin/src/development/route_dev.php';

$slim->run();