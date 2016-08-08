<?php
/*
 * @filesource Widgets/Tags/Controllers/Clicked.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Tags\Models;

use \Kotchasan\Http\Request;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Clicked extends \Kotchasan\Model
{

  public static function save(Request $request)
  {
    if (preg_match('/tags\-([0-9]+)/', $request->post('id')->toString(), $match)) {
      $model = new static;
      $model->db()->createQuery()->update('tags')->set('`count`=`count`+1')->where((int)$match[1])->execute();
    }
  }
}