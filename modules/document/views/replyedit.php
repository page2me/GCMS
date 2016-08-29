<?php
/*
 * @filesource document/views/replyedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Replyedit;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;

/**
 * แก้ไขความคิดเห็น
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
    // antispam
    $antispam = new Antispam();
    // template
    $template = Template::create($index->owner, $index->module, 'replyedit');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/{MODULEID}/' => $index->module_id,
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{QID}/' => $index->index_id,
      '/{RID}/' => $index->id
    ));
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
    if (!empty($index->category_id)) {
      $category = Gcms::ser2Str($index->category);
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $category);
    }
    // breadcrumb ของกระทู้
    Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', 0, 0, 'id='.$index->id), $index->topic);
    // breadcrumb ของหน้า
    $canonical = WEB_URL.'index.php?module='.$index->module.'-edit&amp;rid='.$index->id;
    $topic = '{LNG_Edit} {LNG_comments}';
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
