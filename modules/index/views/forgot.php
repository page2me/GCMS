<?php
/*
 * @filesource index/views/forgot.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Forgot;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;

/**
 * module=forgot
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * หน้าขอรหัสผ่านใหม่
   *
   * @param Request $request
   * @param boolean $modal true แสดงแบบ modal, false (default) แสดงหน้าเว็บปกติ
   * @return object
   */
  public function render(Request $request, $modal = false)
  {
    $index = (object)array(
        'canonical' => WEB_URL.'index.php?module=forgot',
        'topic' => Language::get('Request new password'),
        'description' => self::$cfg->web_description
    );
    $template = Template::create('member', 'member', 'forgotfrm');
    $template->add(array(
      '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))',
      '/{TOPIC}/' => $index->topic,
      '/{EMAIL}/' => Login::$text_username,
      '/{WEBURL}/' => WEB_URL,
      '/{TOKEN}/' => $request->createToken(),
      '/{MODAL}/' => $modal ? 'true' : WEB_URL.'index.php'
    ));
    $index->detail = $template->render();
    $index->keywords = $index->topic;
    if (isset(Gcms::$view)) {
      Gcms::$view->addBreadcrumb($index->canonical, Language::get('Forgot'));
    }
    // เมนู
    $index->menu = 'forgot';
    return $index;
  }
}