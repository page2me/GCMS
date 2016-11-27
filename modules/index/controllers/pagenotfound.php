<?php
/*
 * @filesource index/controllers/pagenotfound.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\PageNotFound;

use \Kotchasan\Language;
use \Kotchasan\Template;

/**
 * หน้าเพจ 404 (Page Not Found)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงข้อผิดพลาด (เช่น 404 page not found)
   *
   * @param string $module ชื่อโมดูลที่เรียก
   * @param string $message ข้อความที่จะแสดง ถ้าไม่กำหนดจะใช้ข้อความของระบบ
   * @return object
   */
  public function init($module, $message = '')
  {
    $template = Template::create($module, '', '404');
    $message = Language::get($message == '' ? 'Sorry, cannot find a page called Please check the URL or try the call again.' : $message);
    $template->add(array(
      '/{TOPIC}/' => $message,
      '/{DETAIL}/' => $message
    ));
    return (object)array(
        'status' => 404,
        'topic' => $message,
        'detail' => $template->render(),
        'description' => $message,
        'keywords' => $message,
        'module' => $module
    );
  }
}