<?php
/*
 * @filesource event/views/day.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Day;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Date;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * แสดงรายการข่าว
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงปฎิทินรายวัน
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function index(Request $request, $index)
  {
    $index = \Event\Day\Model::get($request, $index);
    if ($index) {
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
      // ภาษา
      $lng = Language::getItems(array(
          'MONTH_SHORT',
          'YEAR_OFFSET',
          'FROM_TIME',
          'TO_TIME'
      ));
      // /event/dayitem.html
      $listitem = Template::create('event', $index->module, 'dayitem');
      foreach ($index->items as $item) {
        $listitem->add(array(
          '/{URL}/' => Gcms::createUrl($index->module, '', 0, 0, 'id='.$item->id),
          '/{TOPIC}/' => $item->topic,
          '/{DESCRIPTION}/' => $item->description,
          '/{FROM_TIME}/' => Date::format($item->begin_date, $lng['FROM_TIME']),
          '/{TO_TIME}/' => $item->end_date == '0000-00-00 00:00:00' ? '' : Date::format($item->end_date, $lng['TO_TIME']),
          '/{COLOR}/' => $item->color
        ));
      }
      // /event/day.html
      $template = Template::create('event', $index->module, 'day');
      $template->add(array(
        '/{YEAR}/' => $index->year + $lng['YEAR_OFFSET'],
        '/{MONTH}/' => $lng['MONTH_SHORT'][$index->month],
        '/{DATE}/' => $index->day,
        '/{LIST}/' => $listitem->render(),
        '/{MODULE}/' => $index->module,
        '/{TOPIC}/' => $index->topic
      ));
      $index->detail = $template->render();
      return $index;
    }
    return null;
  }
}