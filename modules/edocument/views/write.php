<?php
/*
 * @filesource edocument/views/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Write;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Text;
use \Kotchasan\Antispam;
use \Kotchasan\Login;
use \Kotchasan\Mime;
use \Kotchasan\ArrayTool;

/**
 * แสดงรายการบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงรายการดาวน์โหลด
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // breadcrumb ของโมดูล
    $menu = Gcms::$menu->moduleMenu($index->module);
    if ($menu) {
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
    } else {
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $index->title);
    }
    // breadcrumb ของหน้า
    $index->canonical = Gcms::createUrl($index->module, 'write');
    Gcms::$view->addBreadcrumb($index->canonical, $index->id == 0 ? '{LNG_Create}' : '{LNG_Edit}');
    // กลุ่มผู้รับ
    $reciever = array();
    foreach (ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status) as $key => $value) {
      $sel = in_array($key, $index->reciever) ? ' selected' : '';
      $reciever[] = '<option value='.$key.$sel.'>'.$value.'</option>';
    }
    // antispam
    $antispam = new Antispam();
    // template
    $template = Template::create($index->owner, $index->module, 'write');
    $template->add(array(
      '/{NO}/' => $index->document_no,
      '/{TOPIC}/' => isset($index->topic) ? $index->topic : '',
      '/{DETAIL}/' => isset($index->detail) ? $index->detail : '',
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{ACCEPT}/' => Mime::getEccept($index->file_typies),
      '/{GROUPS}/' => implode('', $reciever),
      '/{ID}/' => $index->id,
      '/{MODULE_ID}/' => $index->module_id,
      '/{MODULE}/' => $index->module,
      '/{SENDMAIL}/' => $index->id == 0 ? 'checked' : ''
    ));
    Gcms::$view->setContents(array(
      '/:type/' => implode(', ', $index->file_typies),
      '/:size/' => Text::formatFileSize($index->upload_size)
      ), false);
    // คืนค่า
    $index->topic = $index->title;
    $index->detail = $template->render();
    return $index;
  }
}