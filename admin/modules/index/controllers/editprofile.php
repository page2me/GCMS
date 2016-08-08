<?php
/*
 * @filesource index/controllers/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Country;
use \Kotchasan\Province;
use \Kotchasan\Form;
use \Kotchasan\Mime;
use \Gcms\Gcms;

/**
 * แก้ไขข้อมูลส่วนตัวสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผล
   */
  public function render()
  {
    // สมาชิก
    if ($login = Login::isMember()) {
      // id ที่ต้องการ ถ้าไม่มีใช้คนที่ login
      $id = self::$request->get('id', $login['id'])->toInt();
      // อ่านข้อมูลสมาชิก
      $user = \Index\Editprofile\Model::getUser($id);
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
        Gcms::$view->setContents(array(
          '/:type/' => implode(', ', self::$cfg->user_icon_typies)
          ), false);
        return $template->render();
      } else {
        // 404.html
        return \Index\Error\Controller::page404();
      }
    } else {
      // 404.html
      return \Index\Error\Controller::page404();
    }
  }

  /**
   * title bar
   */
  public function title()
  {
    return Language::get('Editing your account');
  }
}