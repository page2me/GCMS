<?php
/*
 * @filesource index/controllers/installing.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Installing;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Html;

/**
 * เพิ่มโมดูลแบบที่สามารถใช้ซ้ำได้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  public function index(Request $request)
  {
    // admin
    if ($request->initSession() && $request->isReferer() && Login::isAdmin()) {
      $result = null;
      // โมดูลหรือ Widget ที่จะติดตั้ง
      $class = $request->post('module')->filter('\\a-zA-Z');
      if (method_exists($class, 'install')) {
        $result = createClass($class)->install($request);
      }
      if (empty($result) || empty($result['value'])) {
        $fieldset = Html::create('aside', array(
            'class' => 'error',
            'innerHTML' => Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed')
        ));
        $ret = array(
          'content' => $fieldset->render()
        );
      } else {
        $fieldset = Html::create('fieldset', array(
            'title' => Language::get('Install')
        ));
        if ($result['value'] > 0) {
          $fieldset->add('aside', array(
            'class' => 'message',
            'innerHTML' => Language::get('<strong>Successfully installed.</strong> Now you can run these modules installed already. (Please refresh)')
          ));
        } else {
          $fieldset->add('aside', array(
            'class' => 'error',
            'innerHTML' => Language::get('Can not install this module. Because this module is already installed. If you want to install this module, you will need to rename installed module to a different name. (This module is to use this name only).')
          ));
        }
        $ret = array(
          'content' => $fieldset->render(),
        );
        if (!empty($result['location'])) {
          $ret['location'] = $result['location'];
        }
      }
      // คืนค่า JSON
      echo json_encode($ret);
    }
  }
}