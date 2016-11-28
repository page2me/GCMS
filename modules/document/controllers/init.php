<?php
/*
 * @filesource document/controllers/init.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Init;

use \Gcms\Gcms;
use \Kotchasan\Login;

/**
 * เริ่มต้นใช้งานโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * Init Module
   *
   * @param array $modules
   */
  public function init($modules)
  {
    if (!empty($modules)) {
      // login
      $login = Login::isMember();
      $writing = false;
      $rss = array();
      foreach ($modules as $module) {
        // RSS Menu
        $rss[$module->module] = '<link rel=alternate type="application/rss+xml" title="'.$module->topic.'" href="'.WEB_URL.$module->module.'.rss">';
        // ตรวจสอบเมนูเขียนเรื่อง
        if (in_array($login['status'], $module->can_write)) {
          Gcms::$member_tabs[$module->module] = array($module->topic, 'Document\Member\View');
          $writing = true;
        }
      }
      if ($writing) {
        // หน้าสำหรับเขียนบทความ
        Gcms::$member_tabs['documentwrite'] = array(null, 'Document\Write\View');
        // ckeditor
        Gcms::$view->addJavascript(WEB_URL.'ckeditor/ckeditor.js');
      }
      if (!empty($rss)) {
        Gcms::$view->setMetas($rss);
      }
    }
  }
}