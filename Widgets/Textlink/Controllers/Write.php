<?php
/*
 * @filesource Widgets/Textlink/Controllers/Write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Controllers;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Write extends \Gcms\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // สมาชิกและสามารถตั้งค่าได้
    if (defined('MAIN_INIT') && Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Create or Edit} {LNG_Text Links}';
      // เมนู
      $this->menu = 'widget';
      // รายการที่ต้องการ
      $index = \Widgets\Textlink\Models\Index::getById(self::$request->get('id')->toInt(), self::$request->get('_name')->topic());
      if ($index) {
        // แสดงผล
        $section = Html::create('section');
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
          'class' => 'breadcrumbs'
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><span class="icon-widgets">{LNG_Widgets}</span></li>');
        $ul->appendChild('<li><span>{LNG_Text Links}</span></li>');
        $ul->appendChild('<li><span>{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h1 class="icon-ads">'.$this->title().'</h1>'
        ));
        // แสดงฟอร์ม
        $section->appendChild(createClass('Widgets\Textlink\Views\Write')->render($index));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}