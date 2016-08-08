<?php
/*
 * @filesource index/models/usericon.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Usericon;

use \Kotchasan\Http\Request;
use \Kotchasan\Http\Response;

/**
 * คลาสสำหรับแสดงรูปภาพสมาชิกจาก id
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  public function index(Request $request)
  {
    if ($request->initSession() && $request->isReferer()) {
      $user = $this->db()->createQuery()
        ->from('user')
        ->where($request->get('id')->toInt())
        ->cacheOn()
        ->toArray()
        ->first('icon');
      if ($user) {
        if (!empty($user['icon']) && is_file(ROOT_PATH.self::$cfg->usericon_folder.$user['icon'])) {
          $icon = ROOT_PATH.self::$cfg->usericon_folder.$user['icon'];
        }
      }
      if (empty($icon)) {
        $icon = is_file(ROOT_PATH.'skin/'.self::$cfg->skin.'/img/noicon.jpg') ? ROOT_PATH.'skin/'.self::$cfg->skin.'/img/noicon.jpg' : ROOT_PATH.'skin/img/noicon.jpg';
      }
      // ตรวจสอบรูป
      $info = getImageSize($icon);
      if (empty($info['error'])) {
        $response = new Response;
        $response->withHeaders(array(
            // cache 1 day
            'Pragma' => 'public',
            'Cache-Control' => 'max-age=86400',
            'Expires' => gmdate('D, d M Y H:i:s GMT', time() + 86400),
            // image header
            'Content-type' => $info['mime']
          ))
          ->setContent(file_get_contents($icon))
          ->send();
      }
    }
  }
}