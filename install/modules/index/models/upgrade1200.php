<?php
/**
 * @filesource index/views/upgrade1200.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade1200;

use \Kotchasan\Language;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Index\Upgrade\Model
{

  /**
   * อัปเกรดเป็นเวอร์ชั่น 12.0.0
   *
   * @return string
   */
  public static function upgrade($db)
  {
    return (object)array(
        'content' => '<li class="correct">Upgrade to Version <b>12.0.0</b> complete.</li>',
        'version' => '12.0.0'
    );
  }
}