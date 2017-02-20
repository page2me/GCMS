<?php
/*
 * @filesource index/controllers/mailserver.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailserver;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;
use \Kotchasan\Config;

/**
 * module=mailserver
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ตั้งค่าระบบอีเมล์
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Setting up the email system}';
      // เลือกเมนู
      $this->menu = 'settings';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
      $ul->appendChild('<li><span>{LNG_Email settings}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-email">'.$this->title.'</h1>'
      ));
      // โหลด config
      $config = Config::load(ROOT_PATH.'settings/config.php');
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Mailserver\View')->render($config));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}