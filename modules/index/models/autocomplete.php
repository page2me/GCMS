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
 * ค้นหาสมาชิก สำหรับ autocomplete
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
        $where = array();
        $select = array('id', 'CONCAT_WS(" ", `pname`, `fname`, `lname`) AS `name`', 'email');
        $order = array();
        foreach (explode(',', $request->post('from', 'name,email')->filter('a-z,')) as $item) {
          if ($item == 'name') {
            $where[] = array('fname', 'LIKE', "%$search%");
            $where[] = array('lname', 'LIKE', "%$search%");
            $order[] = 'name';
          }
          if ($item == 'email') {
            $where[] = array('email', 'LIKE', "%$search%");
            $order[] = 'email';
          }
          if ($item == 'phone') {
            $where[] = array('phone1', 'LIKE', "$search%");
            $select[] = 'phone1';
            $order[] = 'phone1';
          }
        }
        $result = array();
        if (!empty($where)) {
          $query = $this->db()->createQuery()
            ->select($select)
            ->from('user')
            ->where($where, 'OR')
            ->order($order)
            ->limit($request->post('count', 10)->toInt())
            ->toArray();
          foreach ($query->execute() as $item) {
            $datas = array();
            foreach ($item as $key => $value) {
              if ($key != 'id') {
                $value = trim($value);
                if ($value != '') {
                  $datas[] = $value;
                }
              }
            }
            if (!empty($datas)) {
              $result[$item['id']] = array(
                'id' => $item['id'],
                'name' => implode(', ', $datas),
              );
            }
          }
        }
        // คืนค่า JSON
        echo json_encode($result);
      }
    }
  }
}