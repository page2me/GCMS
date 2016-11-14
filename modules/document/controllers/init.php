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
   */
  public function init()
  {
    // login
    $login = Login::isMember();
    // เขียนได้
    $can_write = file_exists(ROOT_PATH.'modules/document/views/member.php');
    $rss = array();
    foreach (Gcms::$install_owners['document'] as $module) {
      $index = Gcms::$install_modules[$module];
      // RSS Menu
      $topic = empty($index->menu_text) ? ucwords($module) : $index->menu_text;
      $rss[$module] = '<link rel=alternate type="application/rss+xml" title="'.$topic.'" href="'.WEB_URL.$module.'.rss">';
      if ($can_write) {
        if (in_array($login['status'], $index->can_write)) {
          Gcms::$member_tabs[$module] = array($topic, 'Document\Member\View');
        }
        Gcms::$member_tabs['documentwrite'] = array(null, 'Document\Write\View');
      }
    }
    if ($can_write) {
      // ckeditor
      Gcms::$view->addJavascript(WEB_URL.'ckeditor/ckeditor.js');
    }
    if (!empty($rss)) {
      Gcms::$view->setMetas($rss);
    }
  }
}
