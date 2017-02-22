<?php
/*
 * @filesource index/views/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Gcms\Gcms;
use \Kotchasan\ArrayTool;

/**
 * module=editprofile
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * หน้าแก้ไขข้อมูลส่วนตัว
   *
   * @param Request $request
   * @return object
   */
  public function render(Request $request)
  {
    if ($login = Login::isMember()) {
      // tab ที่เลือก
      $tab = $request->request('tab')->toString();
      $tab = empty($tab) ? ArrayTool::getFirstKey(Gcms::$member_tabs) : $tab;
      $index = (object)array('description' => self::$cfg->web_description, 'tab' => $tab);
      if (!empty($login['fb'])) {
        unset(Gcms::$member_tabs['password']);
      }
      if (isset(Gcms::$member_tabs[$tab])) {
        // topic
        $index->topic = Language::get(Gcms::$member_tabs[$tab][0]);
        // load class
        $index = createClass(Gcms::$member_tabs[$tab][1])->render($request, $index);
        if ($index) {
          // /member/main.html
          $template = Template::create('member', 'member', 'main');
          // รายการ tabs
          $tabs = array();
          foreach (Gcms::$member_tabs AS $key => $values) {
            if (!empty($values[0])) {
              $class = 'tab '.$key.($key == $index->tab ? ' select' : '');
              $tabs[] = '<li class="'.$class.'"><a href="{WEBURL}index.php?module=editprofile&amp;tab='.$key.'">'.Language::get($values[0]).'</a></li>';
            }
          }
          $template->add(array(
            '/{TAB}/' => implode('', $tabs),
            '/{DETAIL}/' => $index->detail,
            '/{TOKEN}/' => $request->createToken(),
          ));
          $index->detail = $template->render();
          $index->keywords = $index->topic;
          // menu
          $index->menu = 'member';
          return $index;
        }
      }
    }
    // ไม่ได้ login
    return createClass('Index\PageNotFound\Controller')->init('index');
  }
}