<?php
/*
 * @filesource index/views/dologin.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Dologin;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;

/**
 * module=dologin
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * หน้า login
   *
   * @param Request $request
   * @return object
   */
  public function render(Request $request)
  {
    $index = (object)array(
        'canonical' => WEB_URL.'index.php?module=dologin',
        'topic' => Language::get('Visitors please login'),
        'description' => self::$cfg->web_description,
        'menu' => 'dologin'
    );
    $template = Template::create('member', 'member', 'loginfrm');
    $template->add(array(
      '/{TOKEN}/' => $request->createToken(),
      '/{EMAIL}/' => Login::$text_username,
      '/{PASSWORD}/' => Login::$text_password,
      '/{REMEMBER}/' => self::$request->cookie('login_remember')->toInt() == 1 ? 'checked' : '',
      '/{FACEBOOK}/' => empty(self::$cfg->facebook_appId) ? 'hidden' : 'facebook',
      '/{TOPIC}/' => $index->topic,
      '/{SUBTITLE}/' => $index->description
    ));
    $index->detail = $template->render();
    $index->keywords = $index->topic;
    if (isset(Gcms::$view)) {
      Gcms::$view->addBreadcrumb($index->canonical, Language::get('Sign In'));
    }
    return $index;
  }
}