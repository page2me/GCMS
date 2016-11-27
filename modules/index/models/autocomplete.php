<?php
/*
 * @filesource index/models/autocomplete.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Autocomplete;

use \Kotchasan\Http\Request;
use \Gcms\Login;

/**
 * Description
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ค้นหาสมาชิก สำหรับ autocomplete
   * คืนค่าเป็น JSON
   *
   * @param Request $request
   */
  public function findUser(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && Login::isMember()) {
      $search = $request->post('name')->topic();
      if ($search != '') {
        $query = $this->db()->createQuery()
          ->select('id', 'CONCAT_WS(" ", `pname`, `fname`, `lname`) AS `name`', 'email')
          ->from('user')
          ->where(array(
            array('fname', 'LIKE', "%$search%"),
            array('lname', 'LIKE', "%$search%"),
            array('email', 'LIKE', "%$search%")
            ), 'OR')
          ->order('name', 'email')
          ->limit(10)
          ->toArray();
        $result = array();
        foreach ($query->execute() as $item) {
          $name = trim($item['name']);
          $result[$item['id']] = array(
            'id' => $item['id'],
            'name' => $name == '' ? $item['email'] : $name
          );
        }
        // คืนค่า JSON
        echo json_encode($result);
      }
    }
  }
}