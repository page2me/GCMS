<?php
/**
 * @filesource documentation/controllers/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Documentation\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Html;
use \Kotchasan\Login;
use \Gcms\Gcms;

/**
 * module=documentation-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มสร้าง/แก้ไข เนื้อหา
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ค่าที่ส่งมา
    $id = $request->get('id')->toInt();
    $module_id = $request->get('mid')->toInt();
    $category_id = $request->get('cat')->toInt();
    // ตรวจสอบรายการที่เลือก
    $index = \Documentation\Admin\Write\Model::get($module_id, $id, $category_id);
    // login
    $login = Login::isMember();
    // สมาชิกและสามารถตั้งค่าได้
    if ($index && Gcms::canConfig($login, $index, 'can_write')) {
      if (!empty($index->id)) {
        $index->details = \Documentation\Admin\Write\Model::details((int)$index->module_id, (int)$index->id, reset(self::$cfg->languages));
      }
      $title = '{LNG_'.(empty($index->id) ? 'Create' : 'Edit').'}';
      // ข้อความ title bar
      $this->title = $title.' {LNG_Documentation}';
      // เลือกเมนู
      $this->menu = 'modules';
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-documents">{LNG_Module}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=document-settings&mid='.$index->module_id.'}">'.ucfirst($index->module).'</a></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=document-setup&mid='.$index->module_id.'}">{LNG_Contents}</a></li>');
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
          'innerHTML' => '<a id=tab_detail_'.$item.' href="{BACKURL?module=documentation-write&qid='.$index->id.'&tab=detail_'.$item.'}">{LNG_Detail}&nbsp;<img src='.WEB_URL.'language/'.$item.'.gif alt='.$item.'></a>'
        ));
      }
      $ul->add('li', array(
        'innerHTML' => '<a id=tab_options href="{BACKURL?module=documentation-write&qid='.$index->id.'&tab=options}">{LNG_Other details}</a>'
      ));
      if (!$index) {
        $section->appendChild('<aside class=error>{LNG_Can not be performed this request. Because they do not find the information you need or you are not allowed}</aside>');
      } else {
        $section->appendChild(createClass('Documentation\Admin\Write\View')->render($index));
      }
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}