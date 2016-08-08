<?php
/*
 * @filesource index/views/install.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Install;

use \Kotchasan\Html;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * เพิ่มโมดูลแบบที่สามารถใช้ซ้ำได้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=install
   *
   * @param string $type module หรือ widget
   * @param string $module โมดูลที่ติดตั้ง
   * @return object
   */
  public function render($type, $module)
  {
    $div = Html::create('div', array(
        'class' => 'setup_frm',
        'id' => 'install'
    ));
    if (($type === 'module' && empty(Gcms::$install_modules[$module])) || $type === 'widget') {
      $div->add('aside', array(
        'class' => 'tip',
        'innerHTML' => Language::get('Module or an extension has not been installed correctly the first time. Please click on the button "Install" below to complete installation before.')
      ));
      $div2 = $div->add('div', array(
        'class' => 'padding-right-bottom-left'
      ));
      $div2->add('a', array(
        'class' => 'button ok large',
        'id' => 'install_btn',
        'innerHTML' => '<span class=icon-valid>'.Language::get('Install').'</span>'
      ));
      if ($type === 'module') {
        $div->script("callInstall('".rawurlencode(ucfirst($module).'\Admin\Install\Model')."')");
      } elseif ($type === 'widget') {
        $div->script("callInstall('".rawurlencode('Widgets\\'.ucfirst($module).'\Models\Install')."')");
      }
    } else {
      $div->add('aside', array(
        'class' => 'error',
        'innerHTML' => Language::get('Can not install this module. Because this module is already installed. If you want to install this module, you will need to rename installed module to a different name. (This module is to use this name only).')
      ));
    }
    return $div->render();
  }
}