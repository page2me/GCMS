<?php
/*
 * @filesource index/controllers/intro.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Intro;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Html;

/**
 * ฟอร์มตั้งค่าหน้า intro
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
    // แอดมิน
    if (Login::isAdmin()) {
      // ภาษาที่ต้องการ
      $language = self::$request->get('language', Language::name())->toString();
      if (preg_match('/^[a-z]{2,2}$/', $language)) {
        // intro detail
        $template = ROOT_PATH.DATA_FOLDER.'intro.'.$language.'.php';
        if (is_file($template)) {
          $template = trim(preg_replace('/<\?php exit([\(\);])?\?>/', '', file_get_contents($template)));
        } else {
          $template = '<p style="padding: 20px; text-align: center; font-weight: bold;"><a href="index.php">Welcome<br>ยินดีต้อนรับ</a></p>';
        }
        // แสดงผล
        $section = Html::create('section');
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
          'class' => 'breadcrumbs'
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><span class="icon-settings">{LNG_Site settings}</span></li>');
        $ul->appendChild('<li><span>{LNG_Intro Page}</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h1 class="icon-write">'.$this->title().'</h1>'
        ));
        // แสดงฟอร์ม
        $section->appendChild(createClass('Index\Intro\View')->render($language, $template));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_Enable/Disable Intro Page}';
  }
}