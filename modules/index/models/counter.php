<?php
/*
 * @filesource index/models/counter.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Counter;

use \Kotchasan\File;
use \Kotchasan\Login;

/**
 * ข้อมูล Counter และ Useronline
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * Initial Counter & Useronline
   */
  public static function init()
  {
    if (defined('MAIN_INIT')) {
      // Model
      $model = new static;
      $db = $model->db();
      // ตาราง useronline
      $useronline = $model->getTableName('useronline');
      // วันนี้
      $y = (int)date('Y');
      $m = (int)date('m');
      $d = (int)date('d');
      // โฟลเดอร์ของ counter
      $counter_dir = ROOT_PATH.DATA_FOLDER.'counter';
      // ตรวจสอบวันใหม่
      $c = (int)@file_get_contents($counter_dir.'/index.php');
      if ($d != $c) {
        $f = @fopen($counter_dir.'/index.php', 'wb');
        if ($f) {
          fwrite($f, $d);
          fclose($f);
        }
        if ($d < $c) {
          // วันที่ 1 หรือวันแรกของเดือน
          $f = @opendir($counter_dir);
          if ($f) {
            while (false !== ($text = readdir($f))) {
              if ($text != '.' && $text != '..') {
                if ($text != $y) {
                  // ลบไดเร็คทอรี่ของปีก่อน
                  File::removeDirectory($counter_dir.'/'.$text.'/');
                }
              }
            }
            closedir($f);
          }
        }
        // ตรวจสอบ + สร้าง โฟลเดอร์
        File::makeDirectory("$counter_dir/$y");
        File::makeDirectory("$counter_dir/$y/$m");
        // clear useronline
        $db->emptyTable($useronline);
        // clear visited_today
        $db->updateAll($model->getTableName('index'), array('visited_today' => 0));
      }
      // ip ปัจจุบัน
      $counter_ip = self::$request->getClientIp();
      // session ปัจจุบัน
      $session_id = session_id();
      // วันนี้
      $counter_day = date('Y-m-d');
      // บันทึกลง log
      $counter_log = "$counter_dir/$y/$m/$d.dat";
      if (is_file($counter_log)) {
        // เปิดไฟล์เพื่อเขียนต่อ
        $f = @fopen($counter_log, 'ab');
      } else {
        // สร้างไฟล์ log ใหม่
        $f = @fopen($counter_log, 'wb');
      }
      if ($f) {
        $data = $session_id.chr(1).$counter_ip.chr(1).self::$request->server('HTTP_REFERER').chr(1).self::$request->server('HTTP_USER_AGENT').chr(1).date('H:i:s')."\n";
        fwrite($f, $data);
        fclose($f);
      }
      // อ่าน useronline
      $q2 = $db->createQuery()
        ->selectCount()
        ->from('useronline');
      // อ่าน counter รายการล่าสุด
      $my_counter = $db->createQuery()
        ->from('counter C')
        ->order('C.id DESC')
        ->toArray()
        ->first('C.*', array($q2, 'useronline'));
      if (empty($my_counter)) {
        $my_counter = array(
          'date' => $counter_day,
          'counter' => 0,
          'visited' => 0,
          'pages_view' => 0,
        );
        // ข้อมูลใหม่
        $new = true;
        $user_online = 1;
      } elseif ($my_counter['date'] != $counter_day) {
        // ข้อมูลใหม่ ถ้าวันที่ไม่ตรงกัน
        $new = true;
        $user_online = $my_counter['useronline'];
        $my_counter['pages_view'] = 0;
        $my_counter['visited'] = 0;
      } else {
        $new = false;
        $user_online = $my_counter['useronline'];
      }
      $my_counter['pages_view'] ++;
      $my_counter['time'] = time();
      $my_counter['date'] = $counter_day;
      unset($my_counter['useronline']);
      // ตรวจสอบ ว่าเคยเยี่ยมชมหรือไม่
      if ($new || self::$request->cookie('counter_date')->toInt() != $d) {
        // เข้ามาครั้งแรกในวันนี้, บันทึก counter 1 วัน
        setCookie('counter_date', $d, time() + 3600 * 24, '/');
        // ยังไม่เคยเยี่ยมชมในวันนี้
        $my_counter['visited'] ++;
        $my_counter['counter'] ++;
      }
      // counter
      if ($new) {
        unset($my_counter['id']);
        $db->insert($model->getTableName('counter'), $my_counter);
      } else {
        $db->update($model->getTableName('counter'), $my_counter['id'], $my_counter);
      }
      // เวลาหมดอายุ useronline (2 นาที)
      $validtime = $my_counter['time'] - 120;
      // ลบคนที่หมดเวลาและตัวเอง
      $db->delete($useronline, array(array('time', '<', $validtime), array('session', $session_id)), 0, 'OR');
      // ตัวเอง
      $login = Login::isMember();
      // save useronline
      $db->insert($useronline, array(
        'time' => $my_counter['time'],
        'session' => $session_id,
        'ip' => $counter_ip,
        'member_id' => $login ? $login['id'] : 0,
      ));
      $fmt = '%0'.self::$cfg->counter_digit.'d';
      return (object)array(
          'new_day' => $new,
          'counter' => sprintf($fmt, $my_counter['counter']),
          'counter_today' => sprintf($fmt, $my_counter['visited']),
          'pages_view' => sprintf($fmt, $my_counter['pages_view']),
          'useronline' => sprintf($fmt, $user_online),
      );
    }
  }
}