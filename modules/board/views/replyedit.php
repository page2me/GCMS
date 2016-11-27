<?php
/*
 * @filesource board/views/replyedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Replyedit;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;
use \Kotchasan\Language;

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
    // /board/replyedit.html
    $template = Template::create('board', $index->module->module, 'replyedit');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->module->img_upload_type) ? '' : '$1',
      '/{MODULEID}/' => $index->module_id,
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{QID}/' => $index->index_id,
      '/{RID}/' => $index->id
    ));
    Gcms::$view->setContents(array(
      '/:size/' => $index->module->img_upload_size,
      '/:type/' => implode(', ', $index->module->img_upload_type)
      ), false);
    // breadcrumb ของโมดูล
    if (!Gcms::$menu->isHome($index->module->index_id)) {
      $menu = Gcms::$menu->findTopLevelMenu($index->module->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module->module), $menu->menu_text, $menu->menu_tooltip);
      } else {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module->module), $index->module->topic, $index->module->description);
      }
    }
    // breadcrumb ของหมวดหมู่
    if (!empty($index->category_id)) {
      $category = Gcms::ser2Str($index->category);
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module->module, '', $index->category_id), $category);
    }
    // breadcrumb ของกระทู้
    Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module->module, '', 0, 0, 'wbid='.$index->index_id), $index->topic);
    // breadcrumb ของหน้า
    $canonical = WEB_URL.'index.php?module='.$index->module->module.'-edit&amp;rid='.$index->id;
    $topic = Language::get('Edit').' '.Language::get('Comment');
    Gcms::$view->addBreadcrumb($canonical, $topic);
    // คืนค่า
    return (object)array(
        'module' => $index->module->module,
        'canonical' => $canonical,
        'topic' => $topic.' - '.$index->topic,
        'detail' => $template->render(),
        'keywords' => $index->topic,
        'description' => $index->topic
    );
  }
}