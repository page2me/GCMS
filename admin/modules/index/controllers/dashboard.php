<?php
/*
 * @filesource index/controllers/dashboard.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Dashboard;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Html;

/**
 * module=dashboard
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * Dashboard
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::adminAccess()) {
      // ข้อความ title bar
      $this->title = '{LNG_Dashboard}';
      // เลือกเมนู
      $this->menu = 'dashboard';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-home">{LNG_Home}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-dashboard">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Dashboard\View')->render($request));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}