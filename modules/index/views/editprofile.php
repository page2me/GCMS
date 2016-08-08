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

/**
 * module=editprofile
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
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
      $member_tabs = array_keys(Gcms::$member_tabs);
      $tab = in_array($tab, $member_tabs) ? $tab : reset($member_tabs);
      $index = (object)array('description' => self::$cfg->web_description);
      // รายการ tabs
      $tabs = array();
      if (!empty($login['fb'])) {
        unset(Gcms::$member_tabs['password']);
      }
      foreach (Gcms::$member_tabs AS $key => $values) {
        if ($values[0] != '') {
          if ($key == $tab) {
            $class = "tab select $key";
            $index->topic = Language::get($values[0]);
            $className = $values[1];
          } else {
            $class = "tab $key";
          }
          if (preg_match('/^http:\/\/.*/', $values[1])) {
            $tabs[] = '<li class="'.$class.'"><a href="'.$values[1].'">'.Language::get($values[0]).'</a></li>';
          } else {
            $tabs[] = '<li class="'.$class.'"><a href="{WEBURL}index.php?module=editprofile&amp;tab='.$key.'">'.Language::get($values[0]).'</a></li>';
          }
        }
      }
      if (empty($className)) {
        // FB และแก้ไขรหัสผ่าน
        return createClass('Index\PageNotFound\Controller')->init($request, 'index');
      } else {
        $template = Template::create('member', 'member', 'main');
        $template->add(array(
          '/{TAB}/' => implode('', $tabs),
          '/{DETAIL}/' => createClass($className)->render($request)
        ));
        $index->detail = $template->render();
        $index->keywords = $index->topic;
        // menu
        $index->menu = 'member';
        return $index;
      }
    } else {
      // ไม่ได้ login
      return createClass('Index\PageNotFound\Controller')->init($request, 'index');
    }
  }
}