<?php

$container = $slim->getContainer();

$container['flash'] = function ()
{
    return new \Slim\Flash\Messages();
};

$slim->add( new \App\AuthControl($container) );
$slim->add( new \App\SeasonControl($container) );

$container['db'] = function($c)
{
    $db = $c['settings']['db'];
    $pdoOptions = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    ); 
    $pdo = new \PDO('mysql:host=localhost;dbname=' . $db['name'], $db['user'], $db['password'], $pdoOptions);
//    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['loginManager'] = function($c)
{
    return new App\LoginManager($c['settings']);
};

$container['season'] = function($c) {
    //$seasonManager = new \App\SeasonManager($c['db']);
    $seasonManager = \App\SeasonManager::instance($c['db']);
    $seasonManager->loadActiveSeason();
    return $seasonManager;
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('default');
    $filename = "default-" . date("Y-m-d") . '.log';
    $file_handler = new \Monolog\Handler\StreamHandler("../cgi-bin/logs/" . $filename);
    $logger->pushHandler($file_handler);
    
    return $logger;
};

$container['view'] = new \Slim\Views\PhpRenderer('../cgi-bin/templates/');
$container['view']->addAttribute('slim', $container);
$container['view']->addAttribute('baseUrl', $container->settings['baseUrl']);
