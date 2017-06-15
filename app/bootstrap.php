<?php

/**
 * @author     Jirka Svoboda <code@svobik.com>
 * @copyright  2017 © svobik.com
 * @license    https://www.svobik.com/license.md
 * @timestamp  31/05/2017 13:26
 */

$config = [
    'local-test' => __DIR__ . '/config/test-local.php',
    'dist-test' => __DIR__ . '/config/test-local.php.dist',
    // main config
    'local-main' => __DIR__ . '/config/main-local.php',
    'dist-main' => __DIR__ . '/config/main-local.php.dist',
];

if (!file_exists($config['local-test'])) {
    copy($config['dist-test'], $config['local-test']);
}

if (!file_exists($config['local-main'])) {
    copy($config['dist-main'], $config['local-main']);
}