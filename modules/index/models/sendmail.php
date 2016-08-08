<?php
/*
 * @filesource index/models/sendmail.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Sendmail;

use \Kotchasan\Http\Request;
use \Kotchasan\Antispam;
use \Kotchasan\Language;
use \Kotchasan\Email;
use \Kotchasan\Login;

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
   * ส่งอีเมล์ ตาม ID
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      // ค่าที่ส่งมา
      $subject = $request->post('mail_subject')->topic();
      $detail = nl2br($request->post('mail_detail')->textarea());
      // ตรวจสอบ ค่าที่ส่งมา
      $ret = array();
      $antispam = new Antispam($request->post('mail_antispamid')->toString());
      if (!$antispam->valid($request->post('mail_antispam')->toString())) {
        // Antispam ไม่ถูกต้อง
        $ret['ret_mail_antispam'] = 'this';
        $ret['input'] = 'mail_antispam';
      } else {
        // ตรวจสอบผู้รับ
        $reciever = array();
        foreach (self::getUser($request->post('mail_reciever')->filter('0-9a-z')) as $item) {
          $reciever[] = $item['email'].(empty($item['name']) ? '' : '<'.$item['name'].'>');
        }
        $reciever = implode(',', $reciever);
        // ตรวจสอบค่าที่ส่งมา
        if ($reciever == '') {
          $ret['alert'] = Language::get('Unable to send e-mail, Because you can not send e-mail to yourself or can not find the email address of the recipient.');
          $ret['location'] = WEB_URL.'index.php';
        } elseif ($subject == '') {
          $ret['ret_mail_subject'] = 'this';
          $ret['input'] = 'mail_subject';
        } elseif ($detail == '') {
          $ret['ret_mail_detail'] = 'this';
          $ret['input'] = 'mail_detail';
        } else {
          // ส่งอีเมล์
          $err = Email::send($reciever, $login['email'].(empty($login['displayname']) ? '' : '<'.$login['displayname'].'>'), $subject, $detail);
          if (empty($err)) {
            // เคลียร์ Antispam
            $antispam->delete();
            // ส่งอีเมล์สำเร็จ
            $ret['alert'] = Language::get('Your message was sent successfully');
            $ret['location'] = WEB_URL.'index.php';
          } else {
            // ข้อผิดพลาดการส่งอีเมล์
            echo $err;
          }
        }
      }
      if (!empty($ret)) {
        // คืนค่าเป็น JSON
        echo json_encode($ret);
      }
    }
  }

  /**
   * อ่านข้อมูลสมาชิก สำหรับผู้รับจดหมาย
   * ไม่สามารถอ่านอีเมล์ตัวเองได้
   *
   * @param Request $request
   * @param string|int $id ข้อความ "admin" หรือ ID สมาชิกผู้รับ
   * @return array ถ้าไม่พบคืนค่าแอเรย์ว่าง
   */
  public static function getUser($id)
  {
    $result = array();
    // สมาชิกเท่านั้น
    if (!empty($id) && $login = Login::isMember()) {
      $model = new static;
      $db = $model->db();
      $where = array();
      if ($id == 'admin') {
        $where[] = array('id', 'IN', $db->createQuery()->select('id')->from('user')->where(array('status', 1)));
      } else {
        $where[] = array('id', (int)$id);
      }
      $query = $db->createQuery()
        ->select('id', 'email', 'displayname')
        ->from('user')
        ->where($where)
        ->toArray()
        ->cacheOn();
      foreach ($query->execute() as $item) {
        if ($login['email'] != $item['email']) {
          $result[$item['id']] = array(
            'email' => $item['email'],
            'name' => $item['displayname']
          );
        }
      }
    }
    return $result;
  }
}