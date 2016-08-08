<?php
/*
 * @filesource index/views/login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Login;

use \Gcms\Login;
use \Kotchasan\Template;

/**
 * กรอบสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * ฟอร์มสมาชิก
   *
   * @param array $login
   * @return string
   */
  public function member($login)
  {
    $template = Template::create('member', 'member', 'member');
    if ($template->isEmpty()) {
      $template = Template::create('member', 'member', 'memberfrm');
    }
    $template->add(array(
      '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))',
      '/{WEBTITLE}/' => self::$cfg->web_title,
      '/{SUBTITLE}/' => empty(Login::$login_message) ? self::$cfg->web_description : '<span class=error>'.Login::$login_message.'</span>',
      '/{DISPLAYNAME}/' => empty($login['displayname']) ? $login['email'] : $login['displayname'],
      '/{ID}/' => (int)$login['id'],
      '/{STATUS}/' => $login['status'],
      '/{ADMIN}/' => $login && Login::isAdmin() ? '' : 'hidden',
      '/{WEBURL}/' => WEB_URL,
      '/:name/' => self::$cfg->member_status[1]
    ));
    return $template->render();
  }

  /**
   * ฟอร์มเข้าระบบ
   *
   * @return string
   */
  public function login()
  {
    $template = Template::create('member', 'member', 'login');
    if ($template->isEmpty()) {
      $template = Template::create('member', 'member', 'loginfrm');
    }
    $template->add(array(
      '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))',
      '/{SUBTITLE}/' => empty(Login::$login_message) ? self::$cfg->web_description : '<span class=error>'.Login::$login_message.'</span>',
      '/{EMAIL}/' => Login::$text_username,
      '/{PASSWORD}/' => Login::$text_password,
      '/{TOKEN}/' => self::$request->createToken(),
      '/{REMEMBER}/' => self::$request->cookie('login_remember')->toInt() == 1 ? 'checked' : '',
      '/{FACEBOOK}/' => empty(self::$cfg->facebook_appId) ? 'hidden' : 'facebook'
    ));
    return $template->render();
  }
}