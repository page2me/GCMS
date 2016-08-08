<?php
/*
 * @filesource index/controllers/mailwrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailwrite;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Html;

/**
 * ฟอร์มเขียน/แก้ไข แม่แบบอีเมล์
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
      // รายการที่ต้องการ
      $index = \Index\Mailwrite\Model::getIndex(self::$request->get('id')->toInt());
      // สร้างหรือแก้ไข
      $title = Language::get(empty($index->id) ? 'Create' : 'Edit');
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=mailtemplate&id=0}">{LNG_Email template}</a></li>');
      $ul->appendChild('<li><span>'.$title.'</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$title.' '.$index->name.'</h1>'
      ));
      if ($index) {
        // แสดงฟอร์ม
        $section->appendChild(createClass('Index\Mailwrite\View')->render($index));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_Create or Edit} {LNG_Email Template}';
  }
}