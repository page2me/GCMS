<?php
/*
 * @filesource Widgets/Search/Views/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Search\Views;

use \Kotchasan\Template;
use \Kotchasan\Text;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\View
{

  /**
   * แสดงผล Widget
   *
   * @param array $query_string ข้อมูลที่ส่งมาจากการเรียก Widget
   * @return string
   */
  public static function render($query_string)
  {
    // ฟอร์มค้นหา
    $template = Template::createFromFile(ROOT_PATH.'Widgets/Search/Views/search.html');
    $template->add(array(
      '/{ID}/' => Text::rndname(10),
      '/{SEARCH}/' => self::$request->get('q')->topic(),
      '/{MODULE}/' => empty($query_string['module']) ? 'search' : $query_string['module']
    ));
    return $template->render();
  }
}