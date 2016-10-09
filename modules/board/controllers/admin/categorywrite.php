<?php
/*
 * @filesource board/controllers/admin/categorywrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Admin\Categorywrite;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;
use \Gcms\Gcms;

/**
 * ฟอร์มสร้าง/แก้ไข หมวดหมู่
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
  public function render(Request $request)
  {
    // อ่านรายการที่เลือก
    $index = \Board\Admin\Categorywrite\Model::get($request->get('mid')->toInt(), self::$request->get('id')->toInt());
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_config')) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-board">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=board-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=board-category&mid='.$index->module_id.'}">{LNG_Category}</a></li>');
      $ul->appendChild('<li><span>{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Board\Admin\Categorywrite\View')->render($index));
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
    return '{LNG_Create or Edit} {LNG_Category}';
  }
}