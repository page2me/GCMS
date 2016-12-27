<?php
/*
 * @filesource Widgets/Contact/Models/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Contact\Models;

use \Kotchasan\Http\Request;
use \Kotchasan\Validator;
use \Kotchasan\Language;
use \Kotchasan\Email;

/**
 * Send email to admin
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Model
{

  /**
   * ส่งอีเมล์ ตาม ID
   *
   * @param Request $request
   */
  public function send(Request $request)
  {
    $ret = array();
    // session, token
    if ($request->initSession() && $request->isSafe()) {
      // ค่าที่ส่งมา
      $reciever = $request->post('mail_reciever')->topic();
      $sender = $request->post('mail_sender')->topic();
      $subject = $request->post('mail_subject')->topic();
      $detail = nl2br($request->post('mail_detail')->textarea());
      // ตรวจสอบ ค่าที่ส่งมา
      $tmp = $reciever;
      if (!empty($_SESSION['emails']) && !empty($tmp)) {
        foreach ($_SESSION['emails'] as $name => $email) {
          if ($tmp == $name) {
            $reciever = $name;
            break;
          }
        }
      }
      // ส่งหาแอดมิน
      if ($reciever == 'admin') {
        $query = $this->db()->createQuery()
          ->select('email')
          ->from('user')
          ->where(array('status', 1))
          ->toArray()
          ->cacheOn();
        $reciever = array();
        foreach ($query->execute()as $item) {
          $reciever[] = $item['email'];
        }
        $reciever = implode(',', $reciever);
      }
      // ตรวจสอบค่าที่ส่งมา
      if ($sender == '') {
        $ret['ret_mail_sender'] = 'this';
        $ret['input'] = 'mail_sender';
      } elseif (!Validator::email($sender)) {
        $ret['ret_mail_sender'] = Language::get('Please fill in').' '.Language::get('Email');
        $ret['input'] = 'mail_sender';
      } elseif ($reciever == '') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
        $ret['location'] = WEB_URL.'index.php';
      } elseif ($sender == $reciever) {
        $ret['ret_mail_sender'] = Language::get('Unable to send e-mail, Because you can not send e-mail to yourself or can not find the email address of the recipient.');
        $ret['input'] = 'mail_sender';
      } elseif ($subject == '') {
        $ret['ret_mail_subject'] = 'this';
        $ret['input'] = 'mail_subject';
      } elseif ($detail == '') {
        $ret['ret_mail_detail'] = 'this';
        $ret['input'] = 'mail_detail';
      } else {
        // ส่งอีเมล์
        $err = Email::send($reciever, $sender, $subject, $detail);
        if (empty($err)) {
          // เคลียร์
          $request->removeToken();
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