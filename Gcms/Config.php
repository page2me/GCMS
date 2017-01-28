<?php
/*
 * @filesource Gcms/Config.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

/**
 * Config Class สำหรับ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Config extends \Kotchasan\Config
{
  /**
   * รายชื่อฟิลด์จากตารางสมาชิก สำหรับตรวจสอบการ login
   *
   * @var array
   */
  public $login_fields = array('email', 'phone1');
  /**
   * ตั้งค่า การ login ต่อ 1 IP
   * true ไม่สามารถ login พร้อมกันหลายบัญชีต่อ 1 เครื่องได้
   *
   * @var boolean default false
   */
  public $member_only_ip = false;
  /**
   * สถานะสมาชิก
   * 0 สมาชิกทั่วไป
   * 1 ผู้ดูแลระบบ
   *
   * @var array
   */
  public $member_status = array(
    0 => 'Member',
    1 => 'Administrator'
  );
  /**
   * สีของสมาชิกตามสถานะ
   *
   * @var array
   */
  public $color_status = array(
    0 => '#336600',
    1 => '#FF0000'
  );
  /**
   * ถ้ากำหนดเป็น true บัญชี demo จะสามารถเข้าระบบแอดมินได้
   *
   * @var boolean default false
   */
  public $demo_mode = false;
  /**
   * ความกว้างสูงสุดของรูปประจำตัวสมาชิก
   *
   * @var int
   */
  public $user_icon_w = 50;
  /**
   * ความสูงสูงสุดของรูปประจำตัวสมาชิก
   *
   * @var int
   */
  public $user_icon_h = 50;
  /**
   * ชนิดของรูปถาพที่สามารถอัปโหลดเป็นรูปประจำตัวสมาชิก ได้
   *
   * @var array
   */
  public $user_icon_typies = array('jpg', 'jpeg', 'gif', 'png');
  /**
   * ไดเร็คทอรี่เก็บ icon สมาชิก
   *
   * @var string
   */
  public $usericon_folder = 'datas/member/';
  /**
   * สมาชิกใหม่ต้องยืนยันอีเมล์
   *
   * @var bool
   */
  public $user_activate = true;
  /**
   * กำหนดรูปแบบของ URL ที่สร้างจากระบบ
   * ตามที่กำหนดโดย \Settings->urls
   *
   * @var int
   */
  public $module_url = 0;
  /**
   * กำหนดวิธีการหากเข้าระบบเรียบร้อย
   * 0 (ค่าเริ่มต้น) Ajax Login
   * 1 โหลดหน้าใหม่
   * 2 กลับไปหน้าก่อนหน้า
   *
   * @var int
   */
  public $login_action = 0;
  /**
   * จำนวนหลักของตัวนับคนเยี่ยมชม
   *
   * @var int
   */
  public $counter_digit = 4;
  /**
   * รหัสผู้แนะนำ
   *
   * @var int
   */
  public $member_invitation = 0;
  /**
   * โทรศัพท์
   *
   * @var int
   */
  public $member_phone = 0;
  /**
   * บัตรประชาชน
   *
   * @var int
   */
  public $member_idcard = 0;
  /**
   *
   * @var int
   */
  public $use_ajax = 0;
  /**
   * ชื่อเว็บไซต์
   *
   * @var string
   */
  public $web_title = 'GCMS Ajax CMS';
  /**
   * คำอธิบายเกี่ยวกับเว็บไซต์
   *
   * @var string
   */
  public $web_description = 'ระบบบริหารจัดการเว็บไซต์ (CMS) ด้วย Ajax โดยคนไทย';
  /**
   * template ที่กำลังใช้งานอยู่ (ชื่อโฟลเดอร์)
   *
   * @var string
   */
  public $skin = 'rooster';
  /**
   * กำหนดอายุของแคช (วินาที)
   * 0 หมายถึงไม่มีการใช้งานแคช
   *
   * @var int
   */
  public $cache_expire = 5;
  /**
   * ชื่อสงวน ไม่อนุญาติให้ตั้งเป็นชื่อสมาชิก
   *
   * @var array
   */
  public $member_reserv = array(
    'website',
    'webmaster',
    'cms',
    'gcms',
    'module',
    'website',
    'member',
    'members',
    'register',
    'edit',
    'forgot',
  );
  /**
   * รายการคำหยาบ
   * @var array
   */
  public $wordrude = array(
    'ashole',
    'a s h o l e',
    'a.s.h.o.l.e',
    'bitch',
    'b i t c h',
    'b.i.t.c.h',
    'shit',
    's h i t',
    's.h.i.t',
    'fuck',
    'dick',
    'f u c k',
    'd i c k',
    'f.u.c.k',
    'd.i.c.k',
    'มึง',
    'มึ ง',
    'ม ึ ง',
    'ม ึง',
    'มงึ',
    'มึ.ง',
    'มึ_ง',
    'มึ-ง',
    'มึ+ง',
    'กู',
    'ควย',
    'ค ว ย',
    'ค.ว.ย',
    'คอ วอ ยอ',
    'คอ-วอ-ยอ',
    'ปี้',
    'เหี้ย',
    'ไอ้เหี้ย',
    'เฮี้ย',
    'ชาติหมา',
    'ชาดหมา',
    'ช า ด ห ม า',
    'ช.า.ด.ห.ม.า',
    'ช า ติ ห ม า',
    'ช.า.ติ.ห.ม.า',
    'สัดหมา',
    'สัด',
    'เย็ด',
    'หี',
    'สันดาน',
    'แม่ง',
    'ระยำ',
    'ส้นตีน',
    'แตด',
  );
  /**
   * ข้อความแทนที่คำหยาบ
   *
   * @var string
   */
  public $wordrude_replace = 'xxx';
  /**
   * การแสดงผลบทความสำหรับหน้าแสดงรายการตามวันที่ และ Tags
   */
  public $document_style = 'iconview';
  public $document_cols = 1;
  public $document_rows = 20;
}