#!/usr/bin/env php

<?php
defined('IM_DEBUG') or define('IM_DEBUG', true);
defined('IM_ENV') or define('IM_ENV', 'prod');
defined('CONSOLE_ROOT') or define('CONSOLE_ROOT', __DIR__);
require(CONSOLE_ROOT . "/src/config/console.php");
require(CONSOLE_ROOT . '/vendor/autoload.php');

$application = new Easyim\WebServer();
$application->run();
