<?php
/*
 * @filesource index/views/password.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Password;

use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Orm\Recordset;
use \Kotchasan\Mime;

/**
 * หน้าแก้ไขข้อมูลส่วนตัว
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * แสดงผล
   *
   * @return string
   */
  public function render()
  {
    // อ่านข้อมูลสมาชิก
    $rs = Recordset::create('Index\User\Model');
    $user = $rs->where((int)$_SESSION['login']['id'])->first('id');
    $template = Template::create('member', 'member', 'password');
    $contents = array(
      '/{ACCEPT}/' => Mime::getEccept(self::$cfg->user_icon_typies),
      '/{USER_ICON_TYPIES}/' => sprintf(Language::get('Upload a picture of %s resize automatically'), empty(self::$cfg->user_icon_typies) ? 'jpg' : implode(', ', (self::$cfg->user_icon_typies)))
    );
    // ข้อมูลฟอร์ม
    foreach ($user as $key => $value) {
      if ($key == 'sex') {
        $source = Language::get('SEXES');
        $datas = array();
        foreach ($source as $k => $v) {
          $sel = $k == $value ? ' selected' : '';
          $datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
        }
        $contents['/{'.strtoupper($key).'}/'] = implode('', $datas);
      } elseif ($key === 'subscrib') {
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
    $template->add($contents);
    return $template->render();
  }
}