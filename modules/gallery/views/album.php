<?php
/*
 * @filesource gallery/views/album.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Album;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Grid;
use \Gallery\Index\Controller;

/**
 * แสดงรายการสมาชิก
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
    // ลิสต์ข้อมูล
    $index = \Gallery\Album\Model::get($request, $index);
    // รายการ
    $listitem = Grid::create($index->owner, $index->module, 'albumitem');
    foreach ($index->items as $item) {
      // image
      if (is_file(ROOT_PATH.DATA_FOLDER.'gallery/'.$item->id.'/thumb_'.$item->image)) {
        $img = WEB_URL.DATA_FOLDER.'gallery/'.$item->id.'/thumb_'.$item->image;
      } else {
        $img = WEB_URL.'modules/gallery/img/noimage.jpg';
      }
      $listitem->add(array(
        '/{ID}/' => $item->id,
        '/{SRC}/' => $img,
        '/{URL}/' => Controller::url($index->module, $item->id),
        '/{TOPIC}/' => $item->topic
      ));
    }
    // breadcrumb ของโมดูล
    if (Gcms::isHome($index->module)) {
      $index->canonical = WEB_URL.'index.php'.(empty($qs) ? '' : '?'.implode('&amp;', $qs));
    } else {
      $index->canonical = Gcms::createUrl($index->module, '', 0, 0, (empty($qs) ? '' : implode('&', $qs)));
      $menu = Gcms::$menu->moduleMenu($index->module);
      if ($menu) {
        Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
      } else {
        Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
      }
    }
    // current URL
    $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
    // template
    $template = Template::create($index->owner, $index->module, 'album');
    $template->add(array(
      '/{LIST}/' => $listitem->hasItem() ? $listitem->render() : '<div class="error center">{LNG_Sorry, no information available for this item.}</div>',
      '/{COLS}/' => $index->cols,
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => Gcms::showDetail($index->detail, true, false),
      '/{SPLITPAGE}/' => $uri->pagination($index->totalpage, $index->page),
      '/{MODULE}/' => $index->module,
      '/{WIDTH}/' => $index->icon_width,
      '/{HEIGHT}/' => $index->icon_height,
    ));
    // คืนค่า
    $index->detail = $template->render();
    return $index;
  }
}