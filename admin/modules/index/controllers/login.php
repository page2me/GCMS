<?php
/*
 * @filesource index/controllers/login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Login;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * Login Form
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * ประมวลผลหน้า Login
   */
  public function execute()
  {
    // โหมดตัวอย่าง
    if (empty(Login::$text_username) && empty(Login::$text_password) && !empty(self::$cfg->demo_mode)) {
      Login::$text_username = 'demo';
      Login::$text_password = 'demo';
    }
    return createClass('Index\Login\View')->render();
  }

  /**
   * title bar
   */
  public function title()
  {
    return Language::get('Administrator Area');
  }
}