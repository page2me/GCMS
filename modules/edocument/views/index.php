<?php
/*
 * @filesource edocument/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Index;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Date;
use \Kotchasan\Grid;
use \Kotchasan\Text;

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
    // query ข้อมูล
    $index = \Edocument\Index\Model::getItems($request, $index);
    // /edocument/listitem.html
    $listitem = Grid::create('edocument', $index->module, 'listitem');
    foreach ($index->items as $item) {
      $listitem->add(array(
        '/{ID}/' => $item->id,
        '/{NO}/' => $item->document_no,
        '/{NAME}/' => $item->topic,
        '/{EXT}/' => $item->ext,
        '/{SENDER}/' => $item->displayname == '' ? $item->email : $item->displayname,
        '/{STATUS}/' => $item->status,
        '/{ICON}/' => WEB_URL.'skin/ext/'.(is_file(ROOT_PATH.'skin/ext/'.$item->ext.'.png') ? $item->ext : 'file').'.png',
        '/{DETAIL}/' => $item->detail,
        '/{DATE}/' => Date::format($item->last_update, 'd M Y'),
        '/{SIZE}/' => Text::formatFileSize($item->size)
      ));
    }
    // breadcrumb ของโมดูล
    if (Gcms::$menu->isHome($index->index_id)) {
      $index->canonical = WEB_URL.'index.php';
    } else {
      $index->canonical = Gcms::createUrl($index->module);
      $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
      } elseif ($index->topic != '') {
        Gcms::$view->addBreadcrumb($index->canonical, $index->topic, $index->description);
      }
    }
    // current URL
    $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
    // /edocument/list.html หรือ /edocument/empty.html ถ้าไม่มีข้อมูล
    $template = Template::create('edocument', $index->module, $listitem->hasItem() ? 'list' : 'empty');
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