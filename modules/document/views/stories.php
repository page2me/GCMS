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
    // /document/listitem.html
    $listitem = Grid::create('document', $index->module, 'listitem');
    // คอลัมน์
    $listitem->setCols($index->cols);
    // ลิสต์รายการ
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
    if (isset($index->index_id)) {
      // breadcrumb ของโมดูล
      if (Gcms::$menu->isHome($index->index_id)) {
        $index->canonical = WEB_URL.'index.php';
      } else {
        if (empty($index->category_id)) {
          $index->canonical = Gcms::createUrl($index->module);
        } else {
          if (is_array($index->category_id)) {
            $index->canonical = Gcms::createUrl($index->module, '', 0, 0, 'cat='.implode(',', $index->category_id));
          } else {
            $index->canonical = Gcms::createUrl($index->module, '', $index->category_id);
          }
        }
        $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
        if ($menu) {
          Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
        }
      }
    } elseif (isset($index->tag)) {
      // breadcrumb ของ tags
      $index->canonical = Gcms::createUrl('tag', $index->tag);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
    } elseif (isset($index->d)) {
      // breadcrumb ของ calendar
      $index->canonical = Gcms::createUrl('calendar', $index->alias);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
    }
    if (!empty($index->category_id) && is_int($index->category_id)) {
      // breadcrumb ของหมวดหมู่
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), $index->category, $index->category_description);
    }
    // current URL
    $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
    // /document/list.html หรือ /document/empty.html ถ้าไม่มีข้อมูล
    $template = Template::create('document', $index->module, $listitem->hasItem() ? 'list' : 'empty');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/{LIST}/' => $listitem->render(),
      '/{COLS}/' => $index->cols,
      '/{SPLITPAGE}/' => $uri->pagination($index->totalpage, $index->page),
      '/{MODULE}/' => $index->module
    ));
    // คืนค่า
    return (object)array(
        'canonical' => $index->canonical,
        'module' => $index->module,
        'topic' => $index->topic,
        'description' => $index->description,
        'keywords' => $index->keywords,
        'detail' => $template->render()
    );
  }
}