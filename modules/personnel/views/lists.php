<?php
/*
 * @filesource personnel/views/lists.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\Lists;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Personnel\Index\Controller;

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
   * แสดงรายการบุคลากร
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // หมวดที่เลือก
    $category_id = $request->request('cat')->toInt();
    // หมวดหมู่บุคลากร
    $categories = \Index\Category\Model::categories((int)$index->module_id);
    // รายการ
    $listitem = Template::create($index->owner, $index->module, 'item');
    $n = 0;
    $old_cat = 0;
    $old_order = 0;
    foreach (\Personnel\Lists\Model::getItems($index->module_id, $category_id) as $i => $item) {
      if ($old_cat != $item->category_id) {
        $old_cat = $item->category_id;
        if ($i > 0) {
          $listitem->insertHTML('</ul></article><article>');
        }
        $listitem->insertHTML('<h3>'.(isset($categories[$old_cat]) ? $categories[$old_cat] : '{LNG_Unknown}' ).'</h3><ul>');
      }
      if ($n > 0 && ($old_order != $item->order || ($item->order > 0 && $n % $item->order == 0))) {
        $listitem->insertHTML('</ul><ul>');
        $old_order = $item->order;
        $n = 0;
      }
      // image
      if (is_file(ROOT_PATH.DATA_FOLDER.'personnel/'.$item->picture)) {
        $img = WEB_URL.DATA_FOLDER.'personnel/'.$item->picture;
      } else {
        $img = WEB_URL.'modules/personnel/img/noimage.jpg';
      }
      $listitem->add(array(
        '/{ID}/' => $item->id,
        '/{NAME}/' => $item->name,
        '/{POSITION}/' => $item->position,
        '/{DETAIL}/' => $item->detail,
        '/{ADDRESS}/' => $item->address,
        '/{PHONE}/' => $item->phone,
        '/{EMAIL}/' => $item->email,
        '/{ORDER}/' => $item->order,
        '/{PICTURE}/' => $img,
        '/{URL}/' => Controller::url($index->module, $item->id)
      ));
      $n++;
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
    if ($category_id > 0 && isset($categories[$category_id])) {
      // breadcrumb ของหมวดหมู่
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $category_id), $categories[$category_id]);
    }
    // template
    $template = Template::create($index->owner, $index->module, 'list');
    $template->add(array(
      '/{LIST}/' => $listitem->hasItem() ? $listitem->render() : '<div class="error center">{LNG_Sorry, no information available for this item.}</div>',
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => Gcms::showDetail($index->detail, true, false),
      '/{MODULE}/' => $index->module,
      '/{CATEGORY}/' => isset($categories[$category_id]) ? $categories[$category_id] : '{LNG_Unknown}',
    ));
    // คืนค่า
    $index->detail = $template->render();
    return $index;
  }
}