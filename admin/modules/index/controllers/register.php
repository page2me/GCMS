<?php
/*
 * @filesource index/controllers/register.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Register;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * Register Form
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Create new account}';
      // เลือกเมนู
      $this->menu = 'users';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><a class="icon-user" href="index.php?module=member">{LNG_Users}</a></li>');
      $ul->appendChild('<li><span>{LNG_Register}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-register">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Register\View')->render());
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}