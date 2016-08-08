<?php
/*
 * @filesource index/models/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Member;

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
   * อ่านข้อมูลสมาชิกจาก ID
   *
   * @param int $id
   * @return object|bool ข้อมูลสมาชิก ไม่พบคืนค่า false
   */
  public static function getUserById($id)
  {
    $model = new static;
    return $model->db()->createQuery()->from('user')->where($id)->first();
  }

  /**
   * อ่านข้อมูลสมาชิกจาก activatecode
   *
   * @param string $id
   * @return object|bool ข้อมูลสมาชิก ไม่พบคืนค่า false
   */
  public static function getUserByActivateCode($id)
  {
    $model = new static;
    return $model->db()->createQuery()->from('user')->where(array('activatecode', $id))->first();
  }

  /**
   * Activate สมาชิก
   *
   * @param array $user ข้อมูลสมาชิก
   */
  public static function activateUser($user)
  {
    $model = new static;
    $model->db()->update($model->getFullTableName('user'), $user->id, array('activatecode' => ''));
  }
}