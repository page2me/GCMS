<?php
/*
 * @filesource index/controllers/forgot.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Forgot;

/**
 * action=forgot
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * Forgot Form
   *
   * @return string
   */
  public function execute()
  {
    // ข้อความ title bar
    $this->title = '{LNG_Request new password}';
    // ฟอร์ม
    return createClass('Index\Forgot\View')->render();
  }
}