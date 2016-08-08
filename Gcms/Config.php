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
  public $module_url = 1;
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
   * เวลาสำหรับการตรวจสอบคนออนไลน์ (วินาที)
   * 0 หมายถึงไม่มีการตรวจสอบคนออนไลน์
   *
   * @var int
   */
  public $counter_refresh_time = 30;
  /**
   * ช่วงเวลาบอกว่าคนออนไลน์หมดอายุ (วินาที)
   * ควรมากกว่า $counter_refresh_time อย่างน้อยเท่าตัว
   *
   * @var int
   */
  public $counter_gap = 60;
}