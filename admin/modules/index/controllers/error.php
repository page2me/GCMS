<?php
/*
 * @filesource index/controllers/error.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Error;

use \Kotchasan\Template;
use \Kotchasan\Language;

/**
 * Error Controller ของส่วนแอดมิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * Index
   */
  public static function page404()
  {
    $section = Template::create('', '', '404');
    $section->add(array(
      '/{CONTENT}/' => Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed')
    ));
    return $section->render();
  }
}