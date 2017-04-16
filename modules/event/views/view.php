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
      // ภาษา
      $lng = Language::getItems(array(
          'MONTH_SHORT',
          'YEAR_OFFSET',
          'FROM_TIME',
          'TO_TIME'
      ));
      // breadcrumb ของโมดูล
      $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
      } else {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $index->topic, $index->description);
      }
      // วันที่
      preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\s([0-9]{2,2}:[0-9]{2,2}):([0-9]{2,2})$/', $index->begin_date, $match);
      $year = (int)$match[1] + $lng['YEAR_OFFSET'];
      $month = $lng['MONTH_SHORT'][(int)$match[2]];
      $date = (int)$match[3];
      Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', 0, 0, "d=$match[1]-$match[2]-$match[3]"), "$date $month $year");
      // breadcrumb ของหน้า
      $index->canonical = Gcms::createUrl($index->module, '', 0, 0, 'id='.$index->id);
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
      // /event/view.html
      $template = Template::create('event', $index->module, 'view');
      $template->add(array(
        '/{TOPIC}/' => $index->topic,
        '/{DETAIL}/' => Text::highlighter($index->detail),
        '/{MODULE}/' => $index->module,
        '/{YEAR}/' => $year,
        '/{MONTH}/' => $month,
        '/{DATE}/' => $date,
        '/{FROM_TIME}/' => Date::format($index->begin_date, $lng['FROM_TIME']),
        '/{TO_TIME}/' => $index->end_date == '0000-00-00 00:00:00' ? '' : Date::format($index->end_date, $lng['TO_TIME']),
        '/{COLOR}/' => $index->color
      ));
      $index->detail = $template->render();
      return $index;
    }
    return null;
  }
}