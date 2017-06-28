<?php

// set date time default zone
date_default_timezone_set("Europe/Prague");

// codeception backward compatibility fix (disable codeception/codeception/shim.php file to be loaded)
$GLOBALS['__composer_autoload_files']['1a296e41175bbbdf647d8b8ed1f41f41'] = true;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

// include main application bootstrap
$config = require(__DIR__ . '/../app/bootstrap.php');

// inculde Yii2 framework specifics
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// load application config
$config = require(__DIR__ . '/../app/config/test-local.php');

// create console application
$application = new yii\console\Application($config);