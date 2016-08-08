<?php
/*
 * @filesource index/controllers/memberstatus.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Memberstatus;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * จัดการสถานะสมาชิก
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
      $ul->appendChild('<li><span class="icon-user">{LNG_Users}</span></li>');
      $ul->appendChild('<li><span>{LNG_Member status}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-users">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Memberstatus\View')->render());
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
    return '{LNG_The members status of the site}';
  }
}