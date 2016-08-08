<?php
/*
 * @filesource board/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Write;

use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;

/**
 * ตั้งกระทู้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แก้ไขความคิดเห็น
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // หมวดที่เลือก
    $cat = $request->get('cat')->toInt();
    // login
    $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
    // สมาชิก true
    $isMember = $login['status'] > -1;
    // หมวดหมู่
    $category_options = array();
    $category = '';
    foreach (\Index\Category\Model::all($index->module_id) as $item) {
      if ($cat == $item->category_id) {
        $category = $item->topic;
        $category_options[] = '<option value='.$item->category_id.'>'.$category.'</option>';
      }
    }
    if (empty($category_options)) {
      $category_options[] = '<option value=0>{LNG_Uncategorized}</option>';
    }
    // antispam
    $antispam = new Antispam();
    // template
    $template = Template::create($index->owner, $index->module, 'write');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{CATEGORIES}/' => implode('', $category_options),
      '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
      '/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->img_upload_type) ? '' : '$1',
      '/{MODULEID}/' => $index->module_id,
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{LOGIN_PASSWORD}/' => $login['password'],
      '/{LOGIN_EMAIL}/' => $login['email']
    ));
    Gcms::$view->setContents(array(
      '/:size/' => $index->img_upload_size,
      '/:type/' => implode(', ', $index->img_upload_type)
      ), false);
    // breadcrumb ของโมดูล
    if (!Gcms::isHome($index->module)) {
      $menu = Gcms::$menu->moduleMenu($index->module);
      if ($menu) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
      }
    }
    // breadcrumb ของหมวดหมู่
    if ($category != '') {
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $category, $category);
    }
    // breadcrumb ของหน้า
    $index->canonical = Gcms::createUrl($index->module, 'write', $index->category_id);
    $index->topic = Language::get('Create topic');
    Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
    // คืนค่า
    $index->detail = $template->render();
    return $index;
  }
}