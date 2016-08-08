<?php
/*
 * @filesource Widgets/Marquee/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Marquee\Views;

use \Kotchasan\Text;

/**
 * Marquee
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\View
{

  /**
   * Marquee
   *
   * @param array $query_string
   * @return string
   */
  public static function render($query_string)
  {
    if (!empty($query_string['text'])) {
      $id = Text::rndname(10);
      $content = '<div id="containner_'.$id.'" class=marquee_containner><div id="scroller_'.$id.'" class=marquee_scroller>'.$query_string['text'].'</div></div>';
      $content .= '<script>new GScroll("containner_'.$id.'","scroller_'.$id.'").play({"scrollto":"'.$query_string['style'].'","speed":'.max(1, (int)$query_string['speed']).'});</script>';
      return $content;
    }
  }
}