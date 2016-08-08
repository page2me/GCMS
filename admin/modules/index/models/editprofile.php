<?php
/*
 * @filesource index/models/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

/**
 * อ่านข้อมูลสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลสมาชิกที่ $user_id
   *
   * @param int $user_id
   * @return object|null คืนค่า Object ของข้อมูล ไม่พบคืนค่า null
   */
  public static function getUser($user_id)
  {
    if (is_int($user_id) && $user_id > 0) {
      // query ข้อมูลสมาชิกที่เลือก
      $model = new static;
      $query = $model->db()->createQuery();
      $array = array(
        'U.id',
        'U.pname',
        'U.fname',
        'U.lname',
        'U.email',
        'U.displayname',
        'U.website',
        'U.company',
        'U.address1',
        'U.address2',
        'U.phone1',
        'U.phone2',
        'U.sex',
        'U.birthday',
        'U.zipcode',
        'U.country',
        'U.status',
        'U.subscrib',
        'U.admin_access',
        'U.provinceID',
        'U.province',
        'U.icon',
        'U.fb',
        'V.email invite'
      );
      $result = $query->select($array)
        ->from('user U')
        ->join('user V', 'LEFT', array('V.id', 'U.invite_id'))
        ->where(array('U.id', $user_id))
        ->limit(1)
        ->toArray()
        ->execute();
      return sizeof($result) == 1 ? (object)$result[0] : null;
    }
    return null;
  }
}