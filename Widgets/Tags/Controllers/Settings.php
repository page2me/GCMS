<?php
/*
 * @filesource Widgets/Tags/Controllers/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Controllers;

use \Kotchasan\Login;
use \Kotchasan\Html;
use \Kotchasan\Language;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Kotchasan\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // สมาชิกและสามารถตั้งค่าได้
    if (defined('MAIN_INIT') && Login::isAdmin()) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-widgets">{LNG_Widgets}</span></li>');
      $ul->appendChild('<li><span>{LNG_Tags}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-tags">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Widgets\Tags\Views\Settings')->render());
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
    return Language::replace('list of all :name', array(':name' => Language::get('Tags')));
  }
}