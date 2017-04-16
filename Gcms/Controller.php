<?php
/**
 * @filesource Gcms/Controller.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

/**
 * Controller base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
  protected $title;
  protected $menu;

  /**
   * ข้อความ title bar
   *
   * @return string
   */
  public function title()
  {
    return $this->title;
  }

  /**
   * ชื่อเมนูที่เลือก
   *
   * @return string
   */
  public function menu()
  {
    return $this->menu;
  }
}