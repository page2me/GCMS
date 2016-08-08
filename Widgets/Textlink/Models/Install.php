<?php
/*
 * @filesource Widgets/Textlink/Models/Install.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Models;

use \Kotchasan\Http\Request;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Install extends \Kotchasan\Model
{

  /**
   * ติดตั้งโมดูล
   *
   * @param Request $request
   */
  public function install(Request $request)
  {
    echo __FILE__;
  }
}