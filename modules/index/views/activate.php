<?php
/*
 * @filesource index/views/activate.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Activate;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Language;

/**
 * module=view
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * แสดงข้อมูลสมาชิก
   *
   * @param Request $request
   * @return object
   */
  public function render(Request $request)
  {
    // ตรวจสอบข้อมูล
    $user = \Index\Member\Model::getUserByActivateCode($request->get('id')->topic());
    if ($user) {
      // activate
      \Index\Member\Model::activateUser($user);
      // ข้อมูลแสดงผล (สำเร็จ)
      $details = array(
        '/{DETAIL}/' => Language::get('<b>Congratulations!</b> your members have already confirmed. You can use your email address and password sent with the email address used to login.'),
        '/{CLASS}/' => 'message'
      );
    } else {
      // ข้อมูลแสดงผล (ไม่สำเร็จ)
      $details = array(
        '/{DETAIL}/' => Language::get('<b>Sorry!</b> can not find it registered. Information of registration may have expired or your registration may be confirmed.'),
        '/{CLASS}/' => 'error'
      );
    }
    // template
    $template = Template::create('member', 'member', 'activate');
    $template->add($details);
    // คืนค่า
    return (object)array(
        'detail' => $template->render(),
        'keywords' => self::$cfg->web_title,
        'description' => self::$cfg->web_description,
        'topic' => Language::get('Activate').' '.self::$cfg->web_title,
        'menu' => 'member'
    );
  }
}