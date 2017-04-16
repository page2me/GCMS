<?php
/**
 * @filesource event/views/month.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Month;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Gcms\Gcms;

/**
 * แสดงปฎิทิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงปฎิทิน
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function index(Request $request, $index)
  {
    // breadcrumb ของโมดูล
    if (Gcms::$menu->isHome($index->index_id)) {
      $index->canonical = WEB_URL.'index.php';
    } else {
      $index->canonical = Gcms::createUrl($index->module);
      $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
      if ($menu) {
        Gcms::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
      } else {
        Gcms::$view->addBreadcrumb($index->canonical, $index->topic, $index->description);
      }
    }
    // /event/month.html
    $template = Template::create('event', $index->module, 'month');
    $template->add(array(
      '/{TOPIC}/' => $index->topic,
      '/{DETAIL}/' => $index->detail,
      '/{CALENDAR}/' => \Event\Calendar\Controller::render($request),
      '/{MODULE}/' => $index->module
    ));
    $index->detail = $template->render();
    return $index;
  }
}