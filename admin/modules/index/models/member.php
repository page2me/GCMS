<?php
/*
 * @filesource index/models/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Member;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * ตารางสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'user U';

  /**
   * อ่านข้อมูลสมาชิกที่ $user_id
   *
   * @param int $user_id
   * @return object|null คืนค่า Object ของข้อมูล ไม่พบคืนค่า null
   */
  public static function get($user_id)
  {
    if (is_int($user_id) && $user_id > 0) {
      // query ข้อมูลสมาชิกที่เลือก
      $model = new \Kotchasan\Model;
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
        'U.provinceID',
        'U.province',
        'U.status',
        'U.subscrib',
        'U.admin_access',
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

  /**
   * รับค่าจาก action
   *
   * @param Request $request
   */
  public function action(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo' || !empty($login['fb'])) {
        echo Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $action = $request->post('action')->toString();
        // id ที่ส่งมา
        if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
          // Model
          $model = new \Kotchasan\Model;
          // ตาราง user
          $user_table = $model->getTableName('user');
          if ($action === 'delete') {
            // ลบไอคอนสมาชิก
            $query = $model->db()->createQuery()
              ->select('icon')
              ->from('user')
              ->where(array(
              array('id', $match[1]),
              array('id', '!=', 1),
              array('icon', '!=', '')
            ));
            foreach ($query->toArray()->execute() as $item) {
              @unlink(ROOT_PATH.self::$cfg->usericon_folder.$item['icon']);
            }
            // ลบสมาชิก
            $model->db()->delete($user_table, array(
              array('id', $match[1]),
              array('id', '!=', 1)
              ), 0);
          } elseif ($action === 'accept') {
            // ยอมรับสมาชิกที่เลือก
            $model->db()->update($user_table, array(
              array('id', $match[1]),
              array('fb', '0')
              ), array(
              'activatecode' => ''
            ));
          } elseif ($action === 'ban' || $action === 'unban') {
            // ระงับ/ยกเลิก การใช้งานสมาชิก
            $model->db()->update($user_table, array(
              array('id', $match[1]),
              array('id', '!=', 1)
              ), array(
              'ban' => ($action == 'ban' ? 1 : 0)
              )
            );
          } elseif ($action === 'activate' || $action === 'sendpassword') {
            // ส่งอีเมล์ยืนยันสมาชิก
            $query = $model->db()->createQuery()
              ->select('id', 'email', 'activatecode')
              ->from('user')
              ->where(array(
              array('id', $match[1]),
              array('id', '!=', 1),
              array('fb', '0')
            ));
            $msgs = array();
            foreach ($query->toArray()->execute() as $item) {
              // รหัสผ่านใหม่
              $password = \Kotchasan\Text::rndname(6);
              // ข้อมูลอีเมล์
              $replace = array(
                '/%PASSWORD%/' => $password,
                '/%EMAIL%/' => $item['email']
              );
              $save = array('password' => md5($password.$item['email']));
              if ($action === 'activate' || !empty($item['activatecode'])) {
                // activate หรือ ยังไม่ได้ activate
                $save['activatecode'] = empty($item['activatecode']) ? \Kotchasan\Text::rndname(32) : $item['activatecode'];
                $replace['/%ID%/'] = $save['activatecode'];
                // send mail
                $err = \Gcms\Email::send(1, 'member', $replace, $item['email']);
              } else {
                // send mail
                $err = \Gcms\Email::send(3, 'member', $replace, $item['email']);
              }
              $msgs = array();
              if (empty($err)) {
                // อัปเดทรหัสผ่านใหม่
                $model->db()->update($user_table, $item['id'], $save);
              } else {
                $msgs[] = $err;
              }
              if (empty($msgs)) {
                // ส่งอีเมล์ สำเร็จ
                echo Language::get('Your message was sent successfully');
              } else {
                // มีข้อผิดพลาด
                echo implode("\n", $msgs);
              }
            }
          } elseif ($request->post('module')->toString() === 'status') {
            // เปลี่ยนสถานะสมาชิก
            $model->db()->update($user_table, array(
              array('id', $match[1]),
              array('id', '!=', 1),
              array('fb', '0')
              ), array(
              'status' => (int)$action
            ));
          }
        }
      }
    }
  }
}