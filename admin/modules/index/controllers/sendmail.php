<?php
/*
 * @filesource index/controllers/sendmail.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Sendmail;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * ฟอร์มส่งอีเมล์จากแอดมิน
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
    if ($login = Login::isAdmin()) {
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-email">{LNG_Mailbox}</span></li>');
      $ul->appendChild('<li><span>{LNG_Email send}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-email-sent">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Sendmail\View')->render($login));
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
    return '{LNG_Send email by Admin}';
  }
}