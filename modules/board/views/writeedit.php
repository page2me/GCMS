<?php
/*
 * @filesource board/views/writeedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Writeedit;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;
use \Kotchasan\Language;

/**
 * แก้ไขกระทู้
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
    // login
    $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
    // สมาชิก true
    $isMember = $login['status'] > -1;
    // หมวดหมู่
    $category_options = array();
    foreach (\Index\Category\Model::all($index->module_id) as $item) {
      $categories[$item->category_id] = $item->topic;
      if (Login::isAdmin() || $index->category_id == $item->category_id) {
        $sel = $index->category_id == $item->category_id ? ' selected' : '';
        $category_options[] = '<option value='.$item->category_id.$sel.'>'.$item->topic.'</option>';
      }
    }
    if (empty($category_options)) {
      $category_options[] = '<option value=0>{LNG_Uncategorized}</option>';
    }
    // antispam
    $antispam = new Antispam();
    // วันที่ของบอร์ด
    preg_match('/([0-9]{4,4}\-[0-9]{2,2}\-[0-9]{2,2})\s([0-9]+):([0-9]+)/', date('Y-m-d H:i', $index->create_date), $match);
    // hour
    $hour = array();
    for ($i = 0; $i < 24; $i++) {
      $d = sprintf('%02d', $i);
      $sel = $d == $match[2] ? ' selected' : '';
      $hour[] = '<option value='.$d.$sel.'>'.$d.'</option>';
    }
    // minute
    $minute = array();
    for ($i = 0; $i < 60; $i++) {
      $d = sprintf('%02d', $i);
      $sel = $d == $match[3] ? ' selected' : '';
      $minute[] = '<option value='.$d.$sel.'>'.$d.'</option>';
    }
    // template
    $template = Template::create($index->owner, $index->module, 'writeedit');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/{CATEGORIES}/' => implode('', $category_options),
      '/<MODERATOR>(.*)<\/MODERATOR>/s' => Gcms::canConfig($login, $index, 'moderator') ? '$1' : '',
      '/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->img_upload_type) ? '' : '$1',
      '/{DATE}/' => $match[1],
      '/{HOUR}/' => implode('', $hour),
      '/{MINUTE}/' => implode('', $minute),
      '/{MODULEID}/' => $index->module_id,
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{QID}/' => $index->id
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
      } else {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $index->title);
      }
    }
    // breadcrumb ของหมวดหมู่
    if (!empty($index->category)) {
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $index->category);
    }
    // breadcrumb ของกระทู้
    Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', 0, 0, 'wbid='.$index->id), $index->topic);
    // breadcrumb ของหน้า
    $canonical = WEB_URL.'index.php?module='.$index->module.'-edit&amp;qid='.$index->id;
    $topic = Language::get('Edit');
    Gcms::$view->addBreadcrumb($canonical, $topic);
    // คืนค่า
    return (object)array(
        'module' => $index->module,
        'canonical' => $canonical,
        'topic' => $topic.' - '.$index->topic,
        'detail' => $template->render(),
        'keywords' => $index->topic,
        'description' => $index->topic
    );
  }
}