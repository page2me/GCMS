<?php
/*
 * @filesource index/controllers/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Country;
use \Kotchasan\Province;
use \Kotchasan\Form;
use \Kotchasan\Mime;
use \Gcms\Gcms;

/**
 * module=editprofile
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * แก้ไขข้อมูลส่วนตัวสมาชิก
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // สมาชิก
    if ($login = Login::isMember()) {
      // ข้อความ title bar
      $this->title = '{LNG_Editing your account}';
      // เลือกเมนู
      $this->menu = 'users';
      // id ที่ต้องการ ถ้าไม่มีใช้คนที่ login
      $id = $request->get('id', $login['id'])->toInt();
      // อ่านข้อมูลสมาชิก
      $user = \Index\Member\Model::get($id);
      if ($user && ($login['status'] == 1 || $login['id'] == $user->id)) {
        $template = Template::create('', '', 'editprofile');
        $contents = array();
        foreach ($user as $key => $value) {
          if ($key === 'provinceID' || $key === 'country' || $key === 'sex' || $key === 'status') {
            // select
            if ($key == 'provinceID') {
              $source = Province::all();
            } elseif ($key == 'country') {
              $source = Country::all();
            } elseif ($key == 'sex') {
              $source = Language::get('SEXES');
            } elseif ($key == 'status') {
              $source = self::$cfg->member_status;
            }
            $datas = array();
            foreach ($source as $k => $v) {
              $sel = $k == $value ? ' selected' : '';
              $datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
            }
            $contents['/{'.strtoupper($key).'}/'] = implode('', $datas);
          } elseif ($key === 'admin_access' || $key === 'subscrib') {
            $contents['/{'.strtoupper($key).'}/'] = $value == 1 ? 'checked' : '';
          } elseif ($key === 'icon') {
            if (is_file(ROOT_PATH.self::$cfg->usericon_folder.$value)) {
              $icon = WEB_URL.self::$cfg->usericon_folder.$value;
            } else {
              $icon = WEB_URL.'skin/img/noicon.jpg';
            }
            $contents['/{ICON}/'] = $icon;
          } else {
            $contents['/{'.strtoupper($key).'}/'] = $value;
          }
        }
        $contents['/{ADMIN}/'] = Login::isAdmin() && $user->fb == 0 ? '' : 'readonly';
        $contents['/{HIDDEN}/'] = implode("\n", Form::get2Input());
        $contents['/{ACCEPT}/'] = Mime::getEccept(self::$cfg->user_icon_typies);
        $template->add($contents);
        Gcms::$view->setContentsAfter(array(
          '/:type/' => implode(', ', self::$cfg->user_icon_typies)
        ));
        return $template->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}