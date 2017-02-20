<?php
/*
 * @filesource index/controllers/sendmail.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Sendmail;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Html;

/**
 * module=sendmail
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มส่งอีเมล์จากแอดมิน
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if ($login = Login::adminAccess()) {
      // ข้อความ title bar
      $this->title = '{LNG_Send email by Admin}';
      // เลือกเมนู
      $this->menu = 'email';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-email">{LNG_Mailbox}</span></li>');
      $ul->appendChild('<li><span>{LNG_Email send}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-email-sent">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Sendmail\View')->render($login));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}