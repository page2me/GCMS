<?php
/*
 * @filesource index/views/upgrade2.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade2;

use \Kotchasan\Http\Request;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * อัปเกรด
   *
   * @return string
   */
  public function render(Request $request)
  {
    $content = array();
    if (defined('INSTALL')) {
      $content[] = '<h2>{TITLE}</h2>';
      $content[] = '<p>อัปเกรดเรียบร้อย ก่อนการใช้งานกรุณาตรวจสอบค่าติดตั้งต่างๆให้เรียบร้อยก่อน ทั้งการตั้งค่าเว็บไซต์ และการตั้งค่าโมดูล หากคุณต้องการความช่วยเหลือ คุณสามารถ ติดต่อสอบถามได้ที่ <a href="http://www.goragod.com" target="_blank">http://www.goragod.com</a> หรือ <a href="http://gcms.in.th" target="_blank">http://gcms.in.th</a></p>';
      $content[] = '<ul>';
      // ตรวจสอบการเชื่อมต่อฐานข้อมูล
      $db = \Kotchasan\Database::create(array(
          'username' => $_SESSION['cfg']['db_username'],
          'password' => $_SESSION['cfg']['db_password'],
          'dbname' => $_SESSION['cfg']['db_name'],
          'hostname' => $_SESSION['cfg']['db_server'],
          'prefix' => $_SESSION['prefix']
      ));
      if (!$db->connection()) {
        return createClass('Index\Dberror\View')->render($request);
      }
      // อัปเกรด
      $className = 'Index\Upgrade'.str_replace('.', '', self::$cfg->version).'\Model';
      if (class_exists($className) && method_exists($className, 'upgrade')) {
        $content[] = $className::upgrade($db);
      }
      $content[] = '</ul>';
      $content[] = '<p class=warning>กรุณาลบโฟลเดอร์ <em>install/</em> ออกจาก Server ของคุณ</p>';
      $content[] = '<p>เมื่อเรียบร้อยแล้ว กรุณา<b>เข้าระบบผู้ดูแล</b>เพื่อตั้งค่าที่จำเป็นอื่นๆโดยใช้ขื่ออีเมล์และรหัสผ่านเก่าของคุณ</p>';
      $content[] = '<p class=error><b>คำเตือน</b> ตัวอัปเกรด ไม่สามารถนำเข้าข้อมูลได้ทุกอย่าง หลังการอัปเกรด จะต้องตรวจสอบค่ากำหนดต่างๆ ให้ถูกต้องด้วยตัวเองอีกครั้ง เช่น การตั้งค่าเว็บไซต์ การตั้งค่าโมดูล และ การตั้งค่าหมวดของหมวดหมู่ต่างๆ</p>';
      $content[] = '<p><a href="'.WEB_URL.'admin/index.php?module=system" class="button large admin">เข้าระบบผู้ดูแล</a></p>';
    }
    return (object)array(
        'title' => 'อัปเกรด GCMS เป็นเวอร์ชั่น '.self::$cfg->version.' เรียบร้อย',
        'content' => implode('', $content)
    );
  }
}
