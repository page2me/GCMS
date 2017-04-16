<?php
/**
 * @filesource product/controllers/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=product-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มสร้าง/แก้ไข สินค้า
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ตรวจสอบรายการที่เลือก
    $index = \Product\Admin\Write\Model::get($request->get('mid')->toInt(), $request->get('id')->toInt(), false);
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_write')) {
      if (!empty($index->id)) {
        $index->details = \Product\Admin\Write\Model::details((int)$index->module_id, (int)$index->id, reset(self::$cfg->languages));
      }
      $title = '{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}';
      // ข้อความ title bar
      $this->title = $title.' {LNG_Product}';
      // เลือกเมนู
      $this->menu = 'modules';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-product">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=product-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=product-setup&mid='.$index->module_id.'}">{LNG_List of} {LNG_Product}</a></li>');
      $ul->appendChild('<li><span>'.$title.'</span></li>');
      $header = $section->add('header', array(
        'innerHTML' => '<h1 class="icon-write">'.$this->title.'</h1>'
      ));
      $inline = $header->add('div', array(
        'class' => 'inline'
      ));
      $writetab = $inline->add('div', array(
        'class' => 'writetab'
      ));
      $ul = $writetab->add('ul', array(
        'id' => 'accordient_menu'
      ));
      // ภาษาที่ติดตั้ง
      $index->languages = Gcms::installedLanguage();
      foreach ($index->languages as $item) {
        $ul->add('li', array(
          'innerHTML' => '<a id=tab_detail_'.$item.' href="{BACKURL?module=product-write&qid='.$index->id.'&tab=detail_'.$item.'}">{LNG_Detail}&nbsp;<img src='.WEB_URL.'language/'.$item.'.gif alt='.$item.'></a>'
        ));
      }
      $ul->add('li', array(
        'innerHTML' => '<a id=tab_options href="{BACKURL?module=product-write&qid='.$index->id.'&tab=options}">{LNG_Other details}</a>'
      ));
      if (!$index) {
        $section->appendChild('<aside class=error>{LNG_Can not be performed this request. Because they do not find the information you need or you are not allowed}</aside>');
      } else {
        $section->appendChild(createClass('Product\Admin\Write\View')->render($index));
      }
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}