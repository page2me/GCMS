<?php
/**
 * @filesource Widgets/Tags/Controllers/Tooltip.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Controllers;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;

/**
 * แสดง tooltip ของปฎิทิน (Ajax called)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Tooltip extends \Kotchasan\Controller
{

  /**
   * แสดง Tooltip
   *
   * @param Request $request
   * @return string
   */
  public function get(Request $request)
  {
    if (preg_match('/tags\-([0-9]+)/', $request->post('id')->toString(), $match)) {
      $tag = \Widgets\Tags\Models\Tooltip::get((int)$match[1]);
      if ($tag) {
        echo '<div class=tag-tooltip><h5>'.$tag['tag'].'</h5><p>'.Language::get('Clicked').' <em>'.number_format($tag['count']).'</em> '.Language::get('Count').'</p></div>';
      }
    }
  }
}