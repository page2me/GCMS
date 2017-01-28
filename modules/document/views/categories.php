<?php
/*
 * @filesource document/views/categories.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Categories;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;

/**
 * แสดงรายการหมวดหมู่
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงรายการหมวดหมู่
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // อ่านรายการหมวดหมู่ทั้งหมด
    $categories = \Index\Category\Model::all((int)$index->module_id);
    // /document/categoryitem.html
    $listitem = Template::create('document', $index->module, 'categoryitem');
    // รูปภาพ defalt
    if (is_file(ROOT_PATH.DATA_FOLDER.'document/default_icon.png')) {
      $default_icon = WEB_URL.DATA_FOLDER.'document/default_icon.png';
    } elseif (isset($index->default_icon) && is_file(ROOT_PATH.$index->default_icon)) {
      $default_icon = WEB_URL.$index->default_icon;
    } else {
      $default_icon = WEB_URL.'modules/document/img/default_icon.png';
    }
    // รายการ
    foreach ($categories as $item) {
      if (!empty($item->icon) && is_file(ROOT_PATH.DATA_FOLDER.'document/'.$item->icon)) {
        $icon = WEB_URL.DATA_FOLDER.'document/'.$item->icon;
      } else {
        $icon = $default_icon;
      }
      $listitem->add(array(
        '/{TOPIC}/' => $item->topic,
        '/{DETAIL}/' => $item->detail,
        '/{PICTURE}/' => $icon,
        '/{URL}/' => Gcms::createUrl($index->module, '', $item->category_id),
        '/{COUNT}/' => number_format($item->c1),
        '/{COMMENTS}/' => number_format($item->c2)
      ));
    }
    // /document/category.html
    $template = Template::create('document', $index->module, 'category');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/{LIST}/' => $listitem->render(),
      '/{STYLE}/' => $index->category_display,
      '/{COLS}/' => empty($index->category_cols) ? 1 : $index->category_cols,
      '/{MODULE}/' => $index->module
    ));
    // breadcrumb ของโมดูล
    if (Gcms::$menu->isHome($index->index_id)) {
      $index->canonical = WEB_URL.'index.php';
    } else {
      $index->canonical = Gcms::createUrl($index->module);
      $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
      }
    }
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