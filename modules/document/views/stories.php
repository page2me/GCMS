<?php
/*
 * @filesource document/views/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Stories;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Document\Index\Controller;
use \Kotchasan\Date;
use \Kotchasan\Grid;

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
   * แสดงรายการบทความ
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // วันที่สำหรับเครื่องหมาย new
    $valid_date = time() - $index->new_date;
    // รายการ
    $listitem = Grid::create($index->owner, $index->module, 'listitem');
    foreach ($index->items as $item) {
      if (!empty($item->picture) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$item->picture)) {
        $thumb = WEB_URL.DATA_FOLDER.'document/'.$item->picture;
      } elseif (!empty($index->icon) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$index->icon)) {
        $thumb = WEB_URL.DATA_FOLDER.'document/'.$index->icon;
      } else {
        $thumb = WEB_URL.(isset($index->default_icon) ? $index->default_icon : 'modules/document/img/default_icon.png');
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
        '/{URL}/' => Controller::url($item->module, $item->alias, $item->id),
        '/{TOPIC}/' => $item->topic,
        '/{DATE}/' => Date::format($item->create_date),
        '/{DATEISO}/' => date(DATE_ISO8601, $item->create_date),
        '/{COMMENTS}/' => number_format($item->comments),
        '/{VISITED}/' => number_format($item->visited),
        '/{DETAIL}/' => $item->description,
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
    if (isset($index->tag)) {
      // breadcrumb ของ tags
      $index->canonical = Gcms::createUrl('tag', $index->tag);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
    } elseif (isset($index->d)) {
      // breadcrumb ของ calendar
      $index->canonical = Gcms::createUrl('calendar', $index->alias);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
    } elseif (!empty($index->category_id)) {
      // breadcrumb ของหมวดหมู่
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $index->topic);
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
      '/{MODULE}/' => $index->module
    ));
    // คืนค่า
    $index->detail = $template->render();
    return $index;
  }
}