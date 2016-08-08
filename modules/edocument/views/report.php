<?php
/*
 * @filesource edocument/views/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Report;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
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
   * แสดงรายการดาวน์โหลด
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // รายการ
    $listitem = Grid::create($index->owner, $index->module, 'reportitem');
    foreach ($index->items as $item) {
      $displayname = trim($item->fname.' '.$item->lname);
      $listitem->add(array(
        '/{ID}/' => $item->id,
        '/{NAME}/' => $displayname == '' ? $item->email : $displayname,
        '/{GROUP}/' => isset(self::$cfg->member_status[$item->status]) ? self::$cfg->member_status[$item->status] : '{LNG_Guest}',
        '/{STATUS}/' => $item->status,
        '/{DATE}/' => Date::format($item->last_update, 'd M Y'),
        '/{DOWNLOADS}/' => number_format($item->downloads)
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
      } else {
        Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
      }
    }
    // breadcrumb ของหน้า
    $index->canonical = Gcms::createUrl($index->module, 'report', 0, 0, 'id='.$index->id);
    Gcms::$view->addBreadcrumb($index->canonical, '{LNG_Download Details} '.$index->document_no);
    // current URL
    $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
    // template
    $template = Template::create($index->owner, $index->module, $listitem->hasItem() ? 'report' : 'empty');
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