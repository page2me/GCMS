<?php
/*
 * admin/index.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
if (is_file('../settings/config.php') && is_file('../settings/database.php')) {
  // load Kotchasan
  include '../load.php';
  // Initial Kotchasan Framework
  $app = Kotchasan::createWebApplication(Gcms\Config::create());
  $app->defaultRouter = 'Gcms\Router';
  $app->run();
} elseif (is_file('../install/index.php')) {
  // ติดตั้ง
  header('Location: ../install/index.php');
}
