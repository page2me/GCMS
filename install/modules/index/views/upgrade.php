<?php
/*
 * @filesource index/views/upgrade.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade;

use \Kotchasan\Http\Request;

/**
 * ติดตั้ง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * ติดตั้งเรียบร้อยแล้ว
   *
   * @return string
   */
  public function render(Request $request)
  {
    $content = array();
    if (defined('INSTALL')) {
      $content[] = '<h2>{TITLE}</h2>';
      $content[] = '<p style="margin: 20px 0">คุณได้ทำการติดตั้ง GCMS เป็นที่เรียบร้อยแล้ว เพื่อความปลอดภัย กรุณาลบโฟลเดอร์ <em>install/</em> ออกก่อนดำเนินการต่อ</p>';
      $content[] = '<p class="pretty center"><a href="'.WEB_URL.'admin/index.php?module=system" class="button large green"><span class=icon-config>เข้าระบบผู้ดูแล</span></a></p>';
    }
    return (object)array(
        'title' => 'การปรับรุ่น GCMS '.$version.' &rsaquo; Setup Configuration File',
        'content' => implode('', $content)
    );
  }
}