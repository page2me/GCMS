<?php
/*
 * @filesource index/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Gcms\Gcms;
use \Kotchasan\Template;

/**
 * หน้าเพจจากโมดูล index
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * แสดงผล
   *
   * @param object $index ข้อมูลโมดูล
   */
  public function render($index)
  {
    // template main.html, home/main.html
    $template = Template::create('', $index->module, 'main');
    // canonical
    $index->canonical = Gcms::createUrl($index->module);
    // add template
    $template->add(array(
      // content
      '/{DETAIL}/' => Gcms::showDetail($index->detail, true, false),
      // topic
      '/{TOPIC}/' => $index->topic,
      // module name
      '/{MODULE}/' => $index->module
    ));
    // detail
    $index->detail = $template->render();
    // breadcrumb ของหน้า
    Gcms::$view->addBreadcrumb($index->canonical, $index->topic, $index->description);
    return $index;
  }
}