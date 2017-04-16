<?php
/**
 * @filesource Widgets/Tags/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Views;

use \Kotchasan\Text;

/**
 * โมดูลสำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Gcms\View
{

  /**
   * แสดง Tags
   *
   * @param array $items
   * @return string
   */
  public static function render($items)
  {
    $id = Text::rndname(10);
    $content = '<div id="'.$id.'" class=widget-tags>';
    $content .= implode('', $items);
    $content .= '</div>';
    $content .= '<script>initTags("'.$id.'")</script>';
    return $content;
  }
}