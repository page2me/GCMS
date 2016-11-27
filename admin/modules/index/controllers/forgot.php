<?php
/*
 * @filesource index/controllers/forgot.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Forgot;

/**
 * Forgot Form
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * ประมวลผลหน้า Forgot
   */
  public function execute()
  {
    return createClass('Index\Forgot\View')->render();
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_Request new password}';
  }
}