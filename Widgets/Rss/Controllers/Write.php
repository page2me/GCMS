<?php
/*
 * @filesource Widgets/Rss/Controllers/Write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Rss\Controllers;

use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * เขียน-แก้ไข
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
      $this->title = '{LNG_Create or Edit} {LNG_RSS Tab}';
      // เมนู
      $this->menu = 'widget';
      // รายการที่ต้องการ
      $id = self::$request->get('id')->toInt();
      if ($id == 0) {
        $datas = array(
          'url' => '',
          'topic' => '',
          'index' => '',
          'rows' => 3,
          'cols' => 2,
          'id' => 0
        );
      } elseif (isset(self::$cfg->rss_tabs[$id])) {
        $datas = self::$cfg->rss_tabs[$id];
        $datas['id'] = $id;
      } else {
        $datas = null;
      }
      if ($datas) {
        // แสดงผล
        $section = Html::create('section');
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
          'class' => 'breadcrumbs'
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><span class="icon-widgets">{LNG_Widgets}</span></li>');
        $ul->appendChild('<li><span>{LNG_RSS Tab}</span></li>');
        $ul->appendChild('<li><span>{LNG_'.($id == 0 ? 'Create' : 'Edit').'}</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h1 class="icon-rss">'.$this->title().'</h1>'
        ));
        // แสดงฟอร์ม
        $section->appendChild(createClass('Widgets\Rss\Views\Write')->render($datas));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}