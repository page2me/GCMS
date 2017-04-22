<?php
/**
 * @filesource event/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\View;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Date;
use \Kotchasan\Language;
use \Kotchasan\Text;
use \Gcms\Gcms;

/**
 * แสดง Event ที่เลือก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * หน้าแสดงรายละเอียด
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function index(Request $request, $index)
  {
    // query
    $index = \Event\View\Model::get($request, $index);
    if ($index) {
      // breadcrumb ของโมดูล
      $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
      } else {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $index->topic, $index->description);
      }
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', 0, 0, 'd='.$index->begin_date), Date::format($index->begin_date, 'd M Y'));
      // breadcrumb ของหน้า
      $index->canonical = Gcms::createUrl($index->module, '', 0, 0, 'id='.$index->id);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
      // /event/view.html
      $template = Template::create('event', $index->module, 'view');
      $template->add(array(
        '/{TOPIC}/' => $index->topic,
        '/{DETAIL}/' => Text::highlighter($index->detail),
        '/{MODULE}/' => $index->module,
        '/{DATE}/' => $index->begin_date,
        '/{FROM_TIME}/' => Language::replace('FROM_TIME', array('H:i' => $index->from)),
        '/{TO_TIME}/' => $index->end_date == '0000-00-00' ? '' : Language::replace('TO_TIME', array('H:i' => $index->to)),
        '/{COLOR}/' => $index->color
      ));
      $index->detail = $template->render();
      return $index;
    }
    return null;
  }
}