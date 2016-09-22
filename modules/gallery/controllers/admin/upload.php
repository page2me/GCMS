<?php
/*
 * @filesource gallery/controllers/admin/upload.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Upload;

use \Kotchasan\Login;
use \Kotchasan\Html;
use \Gcms\Gcms;

/**
 * ฟอร์มสร้าง/แก้ไข อัลบัม
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
    // ตรวจสอบรายการที่เลือก
    $index = \Gallery\Admin\Write\Model::get(self::$request->get('mid')->toInt(), self::$request->get('id')->toInt());
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_write')) {
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-gallery">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=gallery-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=gallery-setup&mid='.$index->module_id.'}">{LNG_Album}</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=gallery-write&id='.$index->id.'}">'.$index->topic.'</a></li>');
      $ul->appendChild('<li><span>{LNG_Upload}</span></li>');
      $header = $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Gallery\Admin\Upload\View')->render($index));
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
    return '{LNG_Upload your photos into albums}';
  }
}