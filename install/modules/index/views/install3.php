<?php
/*
 * @filesource index/views/install3.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Install3;

use \Kotchasan\Http\Request;
use \Kotchasan\Validator;

/**
 * ติดตั้ง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * step 3
   *
   * @return string
   */
  public function render(Request $request)
  {
    $content = array();
    if (defined('INSTALL')) {
      if ($request->post('email')->exists()) {
        $_SESSION['password'] = $request->post('password')->topic();
        $_SESSION['email'] = $request->post('email')->url();
      }
      if (empty($_SESSION['email']) || empty($_SESSION['password'])) {
        return createClass('Index\Install2\View')->render($request, 'กรุณากรอก '.(empty($_SESSION['email']) ? 'ที่อยู่อีเมล์' : 'รหัสผ่าน'));
      } elseif (!Validator::email($_SESSION['email'])) {
        return createClass('Index\Install2\View')->render($request, 'ที่อยู่อีเมล์ ไม่ถูกต้อง');
      } elseif (!preg_match('/^[A-Za-z0-9]{4,}$/u', $_SESSION['password'])) {
        return createClass('Index\Install2\View')->render($request, 'รหัสผ่าน ภาษาอังกฤษและตัวเลข ไม่น้อยกว่า 4 หลัก');
      } else {
        $db_username = $request->session('db_username', 'root')->username();
        $db_password = $request->session('db_password')->topic();
        $db_server = $request->session('db_server', 'localhost')->url();
        $db_name = $request->session('db_name', 'gcms')->username();
        $prefix = $request->session('prefix', 'gcms')->filter('a-z0-9');
        $newdb = $request->session('newdb')->toInt();
        $content[] = '<form method=post action=index.php autocomplete=off>';
        $content[] = '<h2>{TITLE}</h2>';
        $content[] = '<p>คุณจะต้องระบุข้อมูลการเชื่อมต่อที่ถูกต้องด้านล่างเพื่อเริ่มดำเนินการติดตั้งฐานข้อมูล&nbsp;&nbsp;<a href="http://gcms.in.th/index.php?module=howto&amp;id=24#setup2" target=_blank><img src="modules/index/views/img/help.png" alt=help></a></p>';
        $content[] = '<p class=item><label for=db_username>ชื่อผู้ใช้</label><span class="g-input icon-user"><input type=text size=50 id=db_username name=db_username value="'.$db_username.'"></span></p>';
        $content[] = '<p class=comment>ชื่อผู้ใช้ของ MySQL ของคุณ</p>';
        $content[] = '<p class=item><label for=db_password>รหัสผ่าน</label><span class="g-input icon-password"><input type=text size=50 id=db_password name=db_password value="'.$db_password.'"></span></p>';
        $content[] = '<p class=comment>รหัสผ่านของ MySQL ของคุณ</p>';
        $content[] = '<p class=item><label for=db_server>โฮสท์ของฐานข้อมูล</label><span class="g-input icon-world"><input type=text size=50 id=db_server name=db_server value="'.$db_server.'"></span></p>';
        $content[] = '<p class=comment>ดาตาเบสเซิร์ฟเวอร์ของคุณ (โฮสท์ส่วนใหญ่ใช้ localhost)</p>';
        $content[] = '<p class=item><label for=db_name>ชื่อฐานข้อมูล</label><span class="g-input icon-database"><input type=text size=50 id=db_name name=db_name value="'.$db_name.'"></span></p>';
        $content[] = '<p class=item><input type=checkbox id=newdb name=newdb value=1'.($newdb == 1 ? ' checked' : '').'><label for=newdb>&nbsp;สร้างฐานข้อมูลใหม่ (ข้อมูลเดิมจะถูกลบออกทั้งหมด)</label></p>';
        $content[] = '<p class=comment>เซิร์ฟเวอร์บางแห่งอาจไม่ยอมให้สร้างฐานข้อมูลใหม่ คุณอาจต้องกำหนดเป็นฐานข้อมูลที่คุณมีอยู่แล้ว</p>';
        $content[] = '<p class=item><label for=prefix>คำนำหน้าตาราง</label><span class="g-input icon-table"><input type=text size=50 id=prefix name=prefix value="'.$prefix.'"></span></p>';
        $content[] = '<p class=comment>ใช้สำหรับแยกฐานข้อมูลของ GCMS ออกจากฐานข้อมูลอื่นๆ หากมีการติดตั้งข้อมูลอื่นๆร่วมกันบนฐานข้อมูลนี้ หรือมีการติดตั้ง GCMS มากกว่า 1 ตัว บนฐานข้อมูลนี้ (ภาษาอังกฤษตัวพิมพ์เล็กและตัวเลขเท่านั้น เช่น cms4)</p>';
        $content[] = '<p class=item><label for=typ>นำเข้าข้อมูล</label><span class="g-input icon-star0"><select id=typ name=typ>';
        $database_typies = array(
          '' => 'ติดตั้ง GCMS โดยไม่มีข้อมูลเริ่มต้นใด ๆ',
          'gcms' => 'เว็บไซต์ทั่วไป',
          'gcmss' => 'เว็บไซต์โรงเรียนหรือ อบต.'
        );
        $typ = $request->session('typ')->toString();
        foreach ($database_typies AS $k => $v) {
          $content[] = '<option value="'.$k.'"'.($typ == $k ? ' selected' : '').'>'.$v.'</option>';
        }
        $content[] = '</select></span></p>';
        $content[] = '<p class=comment>ติดตั้ง GCMS พร้อมกับข้อมูลตัวอย่าง เว็บไซต์โรงเรียนจะมีการติดตั้งโมดูลและข้อมูลตัวอย่างเพิ่มเติมจากเว็บไซต์ปกติ ซึ่งคุณสามารถติดตั้งเพิ่มหรือแก้ไขได้เองในภายหลัง</p>';
        $content[] = '<input type=hidden name=step value=4>';
        $content[] = '<p><input class="button large save" type=submit value=ติดตั้ง.></p>';
        $content[] = '</form>';
      }
    }
    return (object)array(
        'title' => 'ค่ากำหนดของฐานข้อมูล',
        'content' => implode('', $content)
    );
  }
}