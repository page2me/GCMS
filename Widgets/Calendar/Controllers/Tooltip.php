<?php
/*
 * @filesource Widgets/Calendar/Controllers/Tooltip.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Calendar\Controllers;

use \Kotchasan\Http\Request;

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
   * แสดงปฎิทิน
   */
  public function get(Request $request)
  {
    // settings
    $settings = include ROOT_PATH.'Widgets/Calendar/settings.php';
    // calendar tooltip
    if (!empty($settings['controller']) && class_exists($settings['controller'], 'tooltip')) {
      echo createClass($settings['controller'])->tooltip($request, $settings);
    }
  }
}