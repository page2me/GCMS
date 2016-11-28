<?php
/*
 * amp.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
// load Kotchasan
include 'load.php';
// Initial Kotchasan Framework
$app = Kotchasan::createWebApplication(Gcms\Config::create());
$app->defaultController = 'Index\Amp\Controller';
$app->defaultRouter = 'Gcms\Router';
$app->run();
