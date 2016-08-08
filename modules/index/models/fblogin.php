<?php
/*
 * @filesource index/models/fblogin.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Fblogin;

use \Kotchasan\Http\Request;
use \Kotchasan\Text;
use \Kotchasan\Language;

/**
 * Facebook Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  public function chklogin(Request $request)
  {
    $data = $request->post('data')->toString();
    if (!empty($data) && $request->initSession() && $request->isSafe()) {
      // สุ่มรหัสผ่านใหม่
      $login_password = Text::rndname(6);
      // ข้อมูลที่ส่งมา
      $facebook_data = array();
      foreach (explode('&', $data) AS $item) {
        list($k, $v) = explode('=', $item);
        if ($k === 'gender') {
          $facebook_data['sex'] = $v === 'male' ? 'm' : 'f';
        } elseif ($k === 'link') {
          $facebook_data['website'] = str_replace(array('http://', 'https://', 'www.'), '', $v);
        } elseif ($k === 'first_name') {
          $facebook_data['fname'] = $v;
          $facebook_data['displayname'] = $v;
        } elseif ($k === 'last_name') {
          $facebook_data['lname'] = $v;
        } elseif ($k === 'email') {
          $facebook_data['email'] = $v;
        } elseif ($k === 'id') {
          $fb_id = $v;
        } elseif ($k === 'birthday' && preg_match('/^([0-9]+)[\/\-]([0-9]+)[\/\-]([0-9]+)$/', $v, $match)) {
          $facebook_data['birthday'] = "$match[3]-$match[1]-$match[2]";
        }
      }
      // ไม่มีอีเมล์ ใช้ id ของ Facebook
      if (empty($facebook_data['email'])) {
        $facebook_data['email'] = $fb_id;
      }
      // db
      $db = $this->db();
      // table
      $user_table = $this->getFullTableName('user');
      // ตรวจสอบสมาชิกกับ db
      $search = $db->createQuery()
        ->from('user')
        ->where(array('email', $facebook_data['email']), array('displayname', $facebook_data['displayname']), 'OR')
        ->toArray()
        ->first('id', 'email', 'visited', 'fb', 'website');
      if ($search === false) {
        // ยังไม่เคยลงทะเบียน, ลงทะเบียนใหม่
        $facebook_data['id'] = $db->getNextId($this->getTableName('user'));
        $facebook_data['fb'] = 1;
        $facebook_data['subscrib'] = 1;
        $facebook_data['visited'] = 0;
        $facebook_data['ip'] = $request->getClientIp();
        $facebook_data['password'] = md5($login_password.$facebook_data['email']);
        $facebook_data['lastvisited'] = time();
        $facebook_data['create_date'] = $facebook_data['lastvisited'];
        $facebook_data['icon'] = $facebook_data['id'].'.jpg';
        $facebook_data['country'] = 'TH';
        $db->insert($user_table, $facebook_data);
      } elseif ($search['fb'] == 1) {
        // facebook เคยเยี่ยมชมแล้ว อัปเดทการเยี่ยมชม
        $facebook_data['visited'] = $search['visited'] + 1;
        $facebook_data['lastvisited'] = time();
        $facebook_data['ip'] = $request->getClientIp();
        $facebook_data['password'] = md5($login_password.$search['email']);
        $db->update($user_table, $search['id'], $facebook_data);
      } else {
        // ไม่สามารถ login ได้ เนื่องจากมี email อยู่ก่อนแล้ว
        $facebook_data = false;
        $ret['alert'] = str_replace(':name', Language::get('User'), Language::get('This :name is already registered'));
        $ret['isMember'] = 0;
      }
      if (is_array($facebook_data)) {
        // อัปเดท icon สมาชิก
        $data = @file_get_contents('https://graph.facebook.com/'.$fb_id.'/picture');
        if ($data) {
          $f = @fopen(ROOT_PATH.self::$cfg->usericon_folder.$facebook_data['icon'], 'wb');
          if ($f) {
            fwrite($f, $data);
            fclose($f);
          }
        }
        // login
        $facebook_data['password'] = $login_password;
        $_SESSION['login'] = $facebook_data;
        // clear
        $request->removeToken();
        // reload
        $ret['isMember'] = 1;
        $u = $request->post('u')->toString();
        if (preg_match('/module=(do)?login/', $u) || preg_match('/(do)?login\.html/', $u)) {
          $ret['location'] = 'back';
        } else {
          $ret['location'] = 'reload';
        }
      }
      // คืนค่าเป็น json
      echo json_encode($ret);
    }
  }
}