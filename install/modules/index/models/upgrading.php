<?php
/**
 * @filesource index/views/upgrading.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrading;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  public static function upgrade($db, $version)
  {
    if ($version == '10.1.2') {
      // อัปเกรดจาก 10.1.2 (เวอร์ชั่นที่ไม่ได้ใช้ Kotchasan)
      return \Index\Upgrade1012\Model::upgrade($db);
    } elseif ($version < '11.2.0') {
      // อัปเกรดเป็น 11.2.0
      return \Index\Upgrade1120\Model::upgrade($db);
    } elseif ($version < '12.0.0') {
      // อัปเกรดเป็น 12.0.0
      return \Index\Upgrade1200\Model::upgrade($db);
    }
  }
}