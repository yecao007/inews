<?php

use Pagon\App;

define('APP_DIR', __DIR__);

require __DIR__ . '/vendor/autoload.php';
$app = new App(__DIR__ . '/config/default.php');

/**
 * Config
 */
// Load env from file
if (is_file(__DIR__ . '/config/env')) {
    $app->mode(file_get_contents(__DIR__ . '/config/env'));
}

// Load config by enviroment
if (!is_file($conf_file = __DIR__ . '/config/' . $app->mode() . '.php')) {
    echo "No config found! Plz add config/" . $app->mode() . ".php file";
    exit;
} else {
    $app->append(include($conf_file));
}

$app->add('Booster');
$app->assisting();

// Add pretty exception
if ($app->mode() == 'develop') {
    $app->add('PrettyException');
} else {
    error_reporting(E_ALL & ~E_NOTICE);
}

$app->protect('loadOrm', function () {
    global $app;
    $config = $app->database;
    ORM::configure(buildDsn($config));
    ORM::configure('username', $config['username']);
    ORM::configure('password', $config['password']);
    Model::$auto_prefix_models = '\\Model\\';
});

$app->share('pdo', function ($app) {
    $config = $app->database;
    return new PDO(buildDsn($config), $config['username'], $config['password'], $config['options']);
});

GlobalEvents::register($app);

return $app;


/**
 * build Dsn string
 *
 * @param array $config
 * @return string
 */
function buildDsn(array $config)
{
    return sprintf('%s:host=%s;port=%s;dbname=%s', $config['type'], $config['host'], $config['port'], $config['dbname']);
}