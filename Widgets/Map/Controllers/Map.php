<?php
/**
 * @filesource Widgets/Map/Controllers/Map.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Map\Controllers;

use \Kotchasan\Http\Request;
use \Kotchasan\Http\Response;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Map extends \Kotchasan\Controller
{

  /**
   * แสดงผล Google Map
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // ส่งออก เป็น HTML
    $response = new Response;
    $response->withContent(createClass('Widgets\Map\Views\Map')->render($request))->send();
  }
}