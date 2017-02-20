<?php
/*
 * @filesource Widgets/Facebook/Controllers/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Facebook\Controllers;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Gcms\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // สมาชิกและสามารถตั้งค่าได้
    if (defined('MAIN_INIT') && Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Configuring} {LNG_Facebook Page}';
      // เมนู
      $this->menu = 'widget';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-widgets">{LNG_Widgets}</span></li>');
      $ul->appendChild('<li><span>{LNG_Facebook Page}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-facebook">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Widgets\Facebook\Views\Settings')->render());
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}