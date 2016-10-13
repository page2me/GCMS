<?php
/*
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
      return \Index\Upgrade1012\Model::upgrade($db);
    } elseif ($version == '11.0.0') {
      return \Index\Upgrade1100\Model::upgrade($db);
    }
  }
}