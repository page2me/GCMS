<?php
/*
 * @filesource video/controllers/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Video\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * ฟอร์มสร้าง/แก้ไข Video
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
    // ตรวจสอบรายการที่เลือก
    $index = \Video\Admin\Write\Model::get($request->get('mid')->toInt(), $request->get('id')->toInt());
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_upload')) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-video">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=video-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=video-setup&mid='.$index->module_id.'}">{LNG_Video}</a></li>');
      $ul->appendChild('<li><span>{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Video\Admin\Write\View')->render($index));
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
    return '{LNG_Create or Edit} {LNG_Video}';
  }
}