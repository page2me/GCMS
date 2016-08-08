<?php
/*
 * @filesource Widgets/Contact/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Contact\Controllers;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Controller
{

  /**
   * แสดงผล Widget
   *
   * @param array $query_string ข้อมูลที่ส่งมาจากการเรียก Widget
   * @return string
   */
  public function get($query_string)
  {
    // ตรวจสอบผู้รับ
    $emails = array(
      'admin' => self::$cfg->member_status[1]
    );
    if (!empty($query_string['module'])) {
      foreach (explode(',', strip_tags($query_string['module'])) AS $item) {
        if (preg_match('/^(.*)((<|&lt;)(.*)(>|&gt;))/', $item, $match)) {
          $emails[$match[1]] = $match[4];
        } else {
          $emails[$item] = $item;
        }
      }
      $_SESSION['emails'] = $emails;
    }
    // form
    return \Widgets\Contact\Views\Index::render($emails);
  }
}