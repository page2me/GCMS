<?php
/*
 * @filesource board/views/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Stories;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Board\Index\Controller;
use \Kotchasan\Date;
use \Kotchasan\Grid;

/**
 * แสดงรายการกระทู้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงรายการกระทู้
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // ลิสต์รายการ
    $index = \Board\Stories\Model::get($request, $index);
    if (empty($index)) {
      // 404
      return createClass('Index\PageNotFound\Controller')->init($request, 'board');
    } else {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // วันที่สำหรับเครื่องหมาย new
      $valid_date = time() - $index->new_date;
      // รายการ
      $listitem = Grid::create($index->owner, $index->module, 'listitem');
      foreach ($index->items as $item) {
        if (!empty($item->picture) && is_file(ROOT_PATH.DATA_FOLDER.'board/thumb-'.$item->picture)) {
          $thumb = WEB_URL.DATA_FOLDER.'board/thumb-'.$item->picture;
        } elseif ($item->pin > 0) {
          $thumb = WEB_URL.'skin/'.self::$cfg->skin.'/board/img/pin.png';
        } elseif ($item->locked > 0) {
          $thumb = WEB_URL.'skin/'.self::$cfg->skin.'/board/img/lock.png';
        } elseif (!empty($index->icon) && is_file(ROOT_PATH.DATA_FOLDER.'board/'.$index->icon)) {
          $thumb = WEB_URL.DATA_FOLDER.'board/'.$index->icon;
        } else {
          $thumb = WEB_URL.(isset($index->default_icon) ? $index->default_icon : 'modules/board/img/default_icon.png');
        }
        if ((int)$item->create_date > $valid_date && empty($item->comment_date)) {
          $icon = ' new';
        } elseif ((int)$item->last_update > $valid_date || (int)$item->comment_date > $valid_date) {
          $icon = ' update';
        } else {
          $icon = '';
        }
        $listitem->add(array(
          '/{ID}/' => $item->id,
          '/{PICTURE}/' => $thumb,
          '/{URL}/' => Controller::url($index->module, $index->category_id, $item->id),
          '/{TOPIC}/' => $item->topic,
          '/{UID}/' => $item->member_id,
          '/{SENDER}/' => $item->sender,
          '/{STATUS}/' => $item->status,
          '/{DATE}/' => Date::format($item->create_date),
          '/{DATEISO}/' => date(DATE_ISO8601, $item->create_date),
          '/{VISITED}/' => number_format($item->visited),
          '/{REPLY}/' => number_format($item->comments),
          '/{REPLYDATE}/' => $item->comment_date == 0 ? '&nbsp;' : Date::format($item->comment_date),
          '/{REPLYER}/' => $item->comment_date == 0 ? '&nbsp;' : $item->commentator,
          '/{STATUS2}/' => $item->replyer_status,
          '/{RID}/' => $item->commentator_id,
          '/{ICON}/' => $icon
        ));
      }
      // breadcrumb ของโมดูล
      if (Gcms::isHome($index->module)) {
        $index->canonical = WEB_URL.'index.php';
      } else {
        $index->canonical = Gcms::createUrl($index->module);
        $menu = Gcms::$menu->moduleMenu($index->module);
        if ($menu) {
          Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
        }
      }
      // current URL
      $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
      // template
      $template = Template::create($index->owner, $index->module, $listitem->hasItem() ? 'list' : 'empty');
      $template->add(array(
        '/{TOPIC}/' => $index->topic,
        '/{DETAIL}/' => $index->detail,
        '/{LIST}/' => $listitem->render(),
        '/{SPLITPAGE}/' => $uri->pagination($index->totalpage, $index->page),
        '/{NEWTOPIC}/' => empty($index->can_post) ? 'hidden' : '',
        '/{CATEGORY}/' => $index->category_id,
        '/{MODULE}/' => $index->module
      ));
      // breadcrumb ของหมวดหมู่
      if (!empty($index->category_id)) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $index->topic);
      }
      // คืนค่า
      $index->detail = $template->render();
      return $index;
    }
  }
}