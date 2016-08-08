<?php
/*
 * @filesource index/controllers/system.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\System;

use \Kotchasan\Login;
use \Kotchasan\Html;
use \Kotchasan\Config;

/**
 * ตั้งค่าเว็บไซต์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
      $ul->appendChild('<li><span>{LNG_General}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-index">'.$this->title().'</h1>'
      ));
      // โหลด config
      $config = Config::load(ROOT_PATH.'settings/config.php');
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\System\View')->render($config));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_General Site Settings}';
  }
}