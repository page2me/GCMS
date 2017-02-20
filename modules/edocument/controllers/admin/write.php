<?php
/*
 * @filesource edocument/controllers/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=edocument-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มสร้าง/แก้ไข เอกสาร
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ตรวจสอบรายการที่เลือก
    $index = \Edocument\Admin\Write\Model::get($request->get('mid')->toInt(), $request->get('id')->toInt(), true);
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_upload')) {
      $title = '{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}';
      // ข้อความ title bar
      $this->title = $title.' {LNG_E-Document}';
      // เลือกเมนู
      $this->menu = 'modules';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-edocument">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=edocument-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=edocument-setup&mid='.$index->module_id.'}">{LNG_E-Document}</a></li>');
      $ul->appendChild('<li><span>'.$title.'</span></li>');
      $header = $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Edocument\Admin\Write\View')->render($index));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}